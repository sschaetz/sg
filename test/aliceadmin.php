<?php
session_start();

/*
 * Author: Dane Iracleous
 * Date: 2010-7-18
 * Version: 1.1
 * Summary: Visual administration tool to view and edit a SQLite3 database
 *
 */
 
//Edit the following two variables to suit your needs

$DBFilename = "../usr/alice/db.sql3"; //path relative to database file

$password = ""; //password to access admin tool (please change this to something more secure)

$maxAttempts = 6; //maximum number of password attempts to allow before locking user out (set to a lower number for better security)







//Do not edit anything below this line unless you know what you are doing

//version number
$version = 1.1;

//build the basename of this file
$nameArr = explode("?", $_SERVER['PHP_SELF']);
$thisName = $nameArr[0];
$nameArr = explode("/", $thisName);
$thisName = $nameArr[sizeof($nameArr)-1];

////////////////////////////////////////////////////////////////////
//Authorization class to maintain security of access to admin tool
class Authorization
{
	public function grant()
	{
		$_SESSION['auth'] = true;
		unset($_SESSION['locked']);
		unset($_SESSION['tries']);
	}
	public function revoke()
	{
		unset($_SESSION['auth']);
	}
	public function fail()
	{
		if(!isset($_SESSION['tries']))
			$_SESSION['tries'] = 1;
		else
			$_SESSION['tries']++;
		
		if($_SESSION['tries'] >= 6)
			$_SESSION['locked'] = true;
	}
	public function isAuthorized()
	{
		return isset($_SESSION['auth']);
	}
	public function isLocked()
	{
		return isset($_SESSION['locked']);
	}
	public function getAttempts()
	{
		if(isset($_SESSION['tries']))
			return $_SESSION['tries'];
		else
			return 0;
	}
}

////////////////////////////////////////////////////////////////////
//Database class to manage interaction with database
class Database 
{
	protected $hDB;
	protected $lastResult;
	public function __construct($filename) 
	{
		$this->hDB = new SQLite3($filename, SQLITE3_OPEN_READWRITE) or die("Error connecting to database");
	}
	public function __destruct() 
	{
		$this->close();
	}
	public function close() 
	{
		if($this->hDB) 
		{
			$this->hDB->close();
			$this->hDB = NULL;
		}
	}
	public function beginTransaction()  
	{
		$this->query("BEGIN");
	}
	public function commitTransaction() 
	{
		$this->query("COMMIT");
	}
	public function rollbackTransaction() 
	{
		$this->query("ROLLBACK");
	}
	public function query($szQuery)  
	{
		$result = $this->hDB->query($szQuery);
		if(!$result)
			die($this->hDB->lastErrorMsg());
		$this->lastResult = $result;
		return $this->lastResult;
	}
	public function insertRow($query)   
	{
		$this->query($query);
		return $this->hDB->lastInsertRowID();
	}
	public function selectAsRow($szQuery) 
	{
		$this->query($szQuery);
		return $this->nextRow();
	}
	public function selectAsAssoc($szQuery) 
	{
		$this->query($szQuery);
		return $this->nextAssoc();
	}
	public function nextRow() 
	{
		return $this->lastResult->fetchArray();
	}
	public function nextAssoc() 
	{
		return $this->lastResult->fetchArray();
	}
	public function selectAsArray($query)
	{
		$arr = array();
		$this->query($query);
		$i = 0;
		while($arr[$i] = $this->nextAssoc())
		{
			$i++;
		}
		return $arr;
	}
	public function generateStructure($table)
	{
		$query = "PRAGMA table_info('".$table."')";
		$result = $this->selectAsArray($query);
		
		echo "Note: Removing and editing columns is not currently possible in SQLite<br/><br/>";
		
		echo "<table border='0' cellpadding='2' cellspacing='1'>";
		echo "<tr>";
		echo "<td class='tdheader'>Column #</td>";
		echo "<td class='tdheader'>Field</td>";
		echo "<td class='tdheader'>Type</td>";
		echo "<td class='tdheader'>Not Null</td>";
		echo "<td class='tdheader'>Default Value</td>";
		echo "<td class='tdheader'>Primary Key</td>";
		echo "</tr>";
		
		for($i=0; $i<sizeof($result)-1; $i++)
		{
			echo "<tr>";
			for($j=0; $j<6; $j++)
			{
				if($i%2)
					echo "<td class='td1'>".$result[$i][$j]."</td>";
				else
					echo "<td class='td2'>".$result[$i][$j]."</td>";
			}
			echo "</tr>";
		}
		
		echo "</table>";
		echo "<br/>";
		echo "<form action='".$thisName."' method='post'>";
		echo "<input type='hidden' name='tablename' value='".$table."'/>";
		echo "Add <input type='text' name='tablefields' style='width:30px;' value='1'/> field(s) at end of table <input type='submit' value='Go' name='addfields'/>";
		echo "</form>";
	}
	public function generateInsert($table)
	{
		$query = "PRAGMA table_info('".$table."')";
		$result = $this->selectAsArray($query);
		
		echo "<form action='".$thisName."?table=".$table."&insert=1' method='post'>";
		echo "<table border='0' cellpadding='2' cellspacing='1'>";
		echo "<tr>";
		echo "<td class='tdheader'>Field</td>";
		echo "<td class='tdheader'>Type</td>";
		echo "<td class='tdheader'>Value</td>";
		echo "</tr>";
		
		for($i=0; $i<sizeof($result)-1; $i++)
		{
			echo "<tr>";
			if($i%2)
				echo "<td class='td1'>";
			else
				echo "<td class='td2'>";
			echo $result[$i][1];
			echo "</td>";
			if($i%2)
				echo "<td class='td1'>";
			else
				echo "<td class='td2'>";
			echo $result[$i][2];
			echo "</td>";
			if($i%2)
				echo "<td class='td1'>";
			else
				echo "<td class='td2'>";
			echo "<input type='text' name='".$result[$i][1]."' style='width:200px;'/>";
			echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "<input type='submit' value='Insert'/>";
		echo "</form>";
	}
	public function generateEdit($table, $edit, $pk)
	{
		$query = "SELECT * FROM ".$table." WHERE ".$pk."=".$edit;
		$result1 = $this->selectAsAssoc($query);
		
		$query = "PRAGMA table_info('".$table."')";
		$result = $this->selectAsArray($query);
		
		echo "<form action='".$thisName."?table=".$table."&edit=".$edit."&pk=".$pk."&confirm=1' method='post'>";
		
		echo "<table border='0' cellpadding='2' cellspacing='1'>";
		echo "<tr>";
		echo "<td class='tdheader'>Field</td>";
		echo "<td class='tdheader'>Type</td>";
		echo "<td class='tdheader'>Value</td>";
		echo "</tr>";
		
		for($i=0; $i<sizeof($result)-1; $i++)
		{
			echo "<tr>";
			if($i%2)
				echo "<td class='td1'>";
			else
				echo "<td class='td2'>";
			echo $result[$i][1];
			echo "</td>";
			
			if($i%2)
				echo "<td class='td1'>";
			else
				echo "<td class='td2'>";
			echo $result[$i][2];
			echo "</td>";
			
			if($i%2)
				echo "<td class='td1'>";
			else
				echo "<td class='td2'>";
			echo "<input type='text' name='".$result[$i][1]."' style='width:400px;' value='".$result1[$i]."'/>";
			echo "</td>";
			
			echo "</tr>";
		}
		echo "</table>";
		echo "<input type='submit' value='Edit'/> ";
		echo "<a href='".$thisName."?table=".$table."'>Cancel</a>";
		echo "</form>";
	}
	public function generateRename($table)
	{
		echo "<form action='".$thisName."' method='post'>";
		echo "<input type='hidden' name='oldname' value='".$table."'/>";
		echo "Rename table '".$table."' to <input type='text' name='newname' style='width:200px;'/> <input type='submit' value='Rename' name='rename'/>";
		echo "</form>";
	}
	public function generateView($table, $numRows, $startRow, $sort, $order)
	{
		$_SESSION['numRows'] = $numRows;
		$_SESSION['startRow'] = $startRow;
		
		$query = "SELECT * FROM ".$table;
		if($sort!=NULL)
			$query .= " ORDER BY ".$sort;
		if($order!=NULL)
			$query .= " ".$order;
		$query .= " LIMIT ".$startRow.", ".$numRows;
		$arr = $this->selectAsArray($query);
		
		if(sizeof($arr)<=1)
		{
			echo "This table is empty.";
			return;
		}
		echo "<table border='0' cellpadding='2' cellspacing='1'>";
		$query = "PRAGMA table_info('".$table."')";
		$result = $this->selectAsArray($query);
		echo "<tr>";
		echo "<td colspan='2'>";
		echo "</td>";
		for($i=0; $i<sizeof($result)-1; $i++)
		{
			echo "<td class='tdheader'>";
			echo "<a href='".$thisName."?table=".$table."&sort=".$result[$i][1];
			if($sort==$result[$i][1] && $order=="ASC")
				echo "&order=DESC";
			else
				echo "&order=ASC";
			echo "'>".$result[$i][1]."</a>";
			if($sort==$result[$i][1] && $order=="ASC")
				echo " >";
			else if($sort==$result[$i][1] && $order=="DESC")
				echo " <";
			echo "</td>";
			if(intval($result[$i][5])==1)
				$pk = $result[$i][1];
		}
		echo "</tr>";
		
		for($i=0; $i<sizeof($arr)-1; $i++)
		{
			echo "<tr>";
			if($i%2)
				echo "<td class='td1'>";
			else
				echo "<td class='td2'>";
			echo "<a href='".$thisName."?table=".$table."&edit=".$arr[$i][$pk]."&pk=".$pk."'>edit</a>";
			echo "</td>";
			if($i%2)
				echo "<td class='td1'>";
			else
				echo "<td class='td2'>";
			echo "<a href='".$thisName."?table=".$table."&delete=".$arr[$i][$pk]."&pk=".$pk."' style='color:red;'>delete</a>";
			echo "</td>";
			for($j=0; $j<sizeof($result)-1; $j++)
			{
				if($i%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo $arr[$i][$j];
				echo "</td>";
			}
			
			echo "</tr>";
		}
		echo "</table>";
	}
	function generateTableList()
	{
		$query = "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name";
		$result = $this->selectAsArray($query);
		
		$j = 0;
		for($i=0; $i<sizeof($result); $i++)
		{
			if(substr($result[$i]['name'], 0, 7)!="sqlite_" && $result[$i]['name']!="")
			{
				$j++;
			}
		}
		
		if($j==0)
		{
			echo "No tables in database.<br/><br/>";
		}
		else
		{
		echo "<table border='0' cellpadding='2' cellspacing='1'>";
		echo "<tr>";
		echo "<td colspan='6'>";
		echo "</td>";
		echo "</tr>";
		
		for($i=0; $i<sizeof($result); $i++)
		{
			if(substr($result[$i]['name'], 0, 7)!="sqlite_" && $result[$i]['name']!="")
			{
				echo "<tr>";
				if($j%2)
					echo "<td class='td1' style='text-align:left;'>";
				else
					echo "<td class='td2' style='text-align:left;'>";
				echo "<a href='".$thisName."?table=".$result[$i]['name']."'>".$result[$i]['name']."</a><br/>";
				echo "</td>";
				if($j%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<a href='".$thisName."?droptable=".$result[$i]['name']."' style='color:red;'>Drop</a>";
				echo "</td>";
				if($j%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<a href='".$thisName."?emptytable=".$result[$i]['name']."' style='color:red;'>Empty</a>";
				echo "</td>";
				if($j%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<a href='".$thisName."?table=".$result[$i]['name']."&view=structure'>Structure</a>";
				echo "</td>";
				if($j%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<a href='".$thisName."?table=".$result[$i]['name']."&view=browse'>Browse</a>";
				echo "</td>";
				if($j%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<a href='".$thisName."?table=".$result[$i]['name']."&view=insert'>Insert</a>";
				echo "</td>";
				echo "</tr>";
				$j++;
			}
		}
		echo "</table>";
		echo "<br/>";
		}
		echo "<fieldset>";
		echo "<legend><b>Create new table</b></legend>";
		echo "<form action='".$thisName."' method='post'>";
		echo "Name: <input type='text' name='tablename' style='width:200px;'/> ";
		echo "Number of Fields: <input type='text' name='tablefields' style='width:90px;'/> ";
		echo "<input type='submit' name='createtable' value='Go'/>";
		echo "</form>";
		echo "</fieldset>";
	}
}

echo "<html>";
echo "<head>";
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />";
echo "<title>SQLite3Admin: ".$DBFilename."</title>";
//break out of PHP to create stylesheet
?>
<style type="text/css">
body
{
	margin:0px;
	padding:0px;
	font-family:"Courier New", Courier, monospace;
	font-size:14px;
	color:#00FF00;
	background-color:#000000;
}
a
{
	font-weight:bold;
	color:#00FF00;
}
h1
{
	margin:0px;
	padding:0px;
	font-size:24px;
}
h2
{
	margin:0px;
	padding:0px;
	font-size:14px;
	margin-bottom:20px;
}
input, select
{
	background-color:#111111;
	color:#00FF00;
	border-color:#00FF00;
	border-style:solid;
	border-width:1px;
	margin:5px;
}
fieldset
{
	padding:15px;
	border-color:#00FF00;
	border-width:1px;
	border-style:solid;
}
#container
{
	padding:15px;
}
#leftNav
{
	float:left;
	width:180px;
	padding:15px;
	border-color:#00FF00;
	border-width:1px;
	border-style:solid;
}
#content
{
	overflow:hidden;
	padding-left:15px;
}
#contentInner
{
	overflow:hidden;
}
#loginBox
{
	width:330px;
	padding:15px;
	margin-left:auto;
	margin-right:auto;
	margin-top:50px;
	border-color:#00FF00;
	border-width:1px;
	border-style:solid;
}
#main
{
	border-color:#00FF00;
	border-width:1px;
	border-style:solid;
	padding:15px;
	overflow:auto;
}
.td1
{
	border-bottom-color:#00FF00;
	border-bottom-width:1px;
	border-bottom-style:none;
	background-color:#212121;
	text-align:right;
	font-size:12px;
}
.td2
{
	border-bottom-color:#00FF00;
	border-bottom-width:1px;
	border-bottom-style:none;
	background-color:#111111;
	text-align:right;
	font-size:12px;
}
.tdheader
{
	border-color:#00FF00;
	border-width:1px;
	border-style:solid;
	font-weight:bold;
	font-size:12px;
	padding-left:5px;
	padding-right:5px;
}
.confirm
{
	border-color:#00FF00;
	border-width:1px;
	border-style:dashed;
	padding:15px;
	margin-bottom:30px;
}
.tab
{
	display:block;
	width:80px;
	padding:5px;
	border-color:#00FF00;
	border-width:1px;
	border-style:solid;
	margin-right:15px;
	float:left;
	border-bottom-style:none;
	position:relative;
	top:1px;
	padding-bottom:4px;
}
.tab_pressed
{
	display:block;
	width:80px;
	padding:5px;
	border-color:#00FF00;
	border-width:1px;
	border-style:solid;
	margin-right:15px;
	float:left;
	border-bottom-style:none;
	position:relative;
	top:1px;
	background-color:#000000;
}
</style>
<?php
//re-enter PHP
function errorMsg($msg, $exit)
{
	echo "<div class='confirm' style='margin:15px;'>";
	echo $msg;
	echo "</div>";
	if($exit)
		exit();
}
echo "</head>";
echo "<body>";

$auth = new Authorization(); //create authorization object

if(isset($_POST['logout'])) //user has attempted to log out
{
	$auth->revoke();
}
else if(isset($_POST['login'])) //user has attempted to log in
{
	if($_POST['password']==$password)
		$auth->grant();
	else
		$auth->fail();
}

if(!$auth->isAuthorized())
{
	echo "<div id='loginBox'>";
	echo "<h1>SQLite3Admin</h1>";
	echo "<h2>".$DBFilename."</h2>";
	if($auth->isLocked())
	{
		echo "Unfortunately, you have entered an incorrect password too many times. You are locked out. Sorry.";
	}
	else
	{
		$lock = $auth->getAttempts();
		if($lock>0)
			echo $lock." attempts out of ".$maxAttempts.".<br/><br/>";
		echo "<form action='".$thisName."' method='post'>";
		echo "Password: <input type='password' name='password'/>";
		echo "<input type='submit' value='Log In' name='login'/>";
		echo "</form>";
	}
	echo "</div>";
}
else
{
	//First, check to see that the database file is intact and can be used
	if(!file_exists($DBFilename))
	{
		errorMsg("Error: The database file specified, '".$DBFilename."', does not exist. You may not continue until you correctly reference the path to the database file.", true);
	}
	//Check that a sufficient PHP version is installed
	$ver = doubleval(phpversion());
	if($ver < 5.3)
	{
		errorMsg("Error: Your version of PHP, ".$ver." does not support SQLite3. You may not continue until you upgrade.", true);
	}
	//Check the permissions of the database file
	$perms = substr(decoct(fileperms($DBFilename)),4);

	if(intval($perms)<44)
	{
		errorMsg("Error: The database file cannot be read or written-to. You may not continue until you increase the permissions on the database file.", true);	
	}
	if(intval($perms)>=44 && intval($perms)<66)
	{
		errorMsg("Warning: The database file can be read but not written-to. You may continue, but be warned that attempting to edit the database will cause errors.", false);
	}
	//Check to see if user has not changed default password
	if($password=="admin")
	{
		errorMsg("Warning: The password has not been changed from its default value, 'admin'. You may continue, but be warned that this is a security hole.", false);
	}
	else if(strlen($password)>4)
	{
		errorMsg("Warning: The current password is only ".strlen($password)." characters long. You may continue, but be warned that this is a security hole.", false);
	}
	
	$db = new Database($DBFilename); //create the Database object
	
	//Switch board for various operations a user could have requested
	if(isset($_POST['createtable'])) //ver 1.1 bug fix - check for $_POST variables before $_GET variables 
	{
	
	}
	else if(isset($_POST['rename']))
	{
		$query = "ALTER TABLE ".$_POST['oldname']." RENAME TO ".$_POST['newname'];
		$db->query($query);
	}
	else if(isset($_POST['createtableconfirm'])) //create new table
	{
		$num = intval($_GET['rows']);
		$name = $_GET['tablename'];
		//build the query for creating this new table
		$query = "CREATE TABLE ".$name."(";
		
		for($i=0; $i<$num; $i++)
		{
			if($_POST[$i.'_field']!="")
			{
				$query .= $_POST[$i.'_field']." ";
				$query .= $_POST[$i.'_type']." ";
				if(isset($_POST[$i.'_primarykey']))
					$query .= "PRIMARY KEY ";
				if(isset($_POST[$i.'_notnull']))
					$query .= "NOT NULL ";
				if($_POST[$i.'_defaultvalue']!="")
				{
					if($_POST[$i.'_type']=="INTEGER")
						$query .= "default ".$_POST[$i.'_defaultvalue']."  ";
					else
						$query .= "default '".$_POST[$i.'_defaultvalue']."' ";
				}
				$query = substr($query, 0, sizeof($query)-2);
				$query .= ", ";
			}
		}
		$query = substr($query, 0, sizeof($query)-3);
		$query .= ")";
		$db->query($query);
	}
	else if(isset($_POST['addfieldsconfirm'])) //create new table
	{
		$num = intval($_GET['rows']);
		$name = $_GET['tablename'];
		//build the query for creating this new table
		for($i=0; $i<$num; $i++)
		{
			if($_POST[$i.'_field']!="")
			{
				$query = "ALTER TABLE ".$name." ADD COLUMN ".$_POST[$i.'_field']." ";
				$query .= $_POST[$i.'_type']." ";
				if(isset($_POST[$i.'_primarykey']))
					$query .= "PRIMARY KEY ";
				if(isset($_POST[$i.'_notnull']))
					$query .= "NOT NULL ";
				if($_POST[$i.'_defaultvalue']!="")
				{
					if($_POST[$i.'_type']=="INTEGER")
						$query .= "DEFAULT ".$_POST[$i.'_defaultvalue']."  ";
					else
						$query .= "DEFAULT '".$_POST[$i.'_defaultvalue']."' ";
				}
				$db->query($query);
			}
		}
	}
	else if(isset($_GET['droptable']) && isset($_GET['confirm'])) //drop table
	{
		$query = "DROP TABLE ".$_GET['droptable'];
		$db->query($query);
	}
	else if(isset($_GET['emptytable']) && isset($_GET['confirm'])) //empty table
	{
		$query = "DELETE FROM ".$_GET['emptytable'];
		$db->query($query);
		$query = "VACUUM";
		$db->query($query);
	}
	else if(isset($_GET['insert'])) //insert record into table
	{
		$query = "INSERT INTO ".$_GET['table']." (";
		foreach($_POST as $vblname => $value)
		{
			$query .= $vblname.",";
		}
		$query = substr($query, 0, sizeof($query)-2);
		$query .= ") VALUES (";
		foreach($_POST as $vblname => $value)
		{
			if($value=="")
				$query .= "NULL,";
			else
				$query .= "'".$value."',";
		}
		$query = substr($query, 0, sizeof($query)-2);
		$query .= ")";
		
		$db->query($query);
	}
	echo "<div id='container'>";
	echo "<div id='leftNav'>";
	echo "<a href='".$thisName."' style='text-decoration:none;'>";
	echo "<h1>SQLite3Admin</h1>";
	echo "<h2>".$DBFilename."</h2>";
	echo "</a>";

	//Display list of tables
	$query = "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name";
	$result = $db->selectAsArray($query);
	$j=0;
	for($i=0; $i<sizeof($result); $i++)
	{
		if(substr($result[$i]['name'], 0, 7)!="sqlite_" && $result[$i]['name']!="")
		{
			echo "<a href='".$thisName."?table=".$result[$i]['name']."'>".$result[$i]['name']."</a><br/>";
			$j++;
		}
	}
	if($j==0)
	{
		echo "No tables in database.";
	}
	echo "<br/><br/>";
	echo "<form action='".$thisName."' method='post'/>";
	echo "<input type='submit' value='Log Out' name='logout'/>";
	echo "</form>";
	echo "</div>";
	echo "<div id='content'>";
	echo "<div id='contentInner'>";
	
	if(isset($_POST['createtable']) || isset($_POST['addfields']))
	{
		if(isset($_POST['addfields']))
			echo "<h2>Adding new field(s) to table ".$_POST['tablename']."</h2>";
		else
			echo "<h2>Creating new table: ".$_POST['tablename']."</h2>";
		if($_POST['tablefields']=="" || intval($_POST['tablefields'])<=0)
			echo "You must specify the number of table fields.";
		else if($_POST['tablename']=="")
			echo "You must specify a table name.";
		else
		{
			$num = intval($_POST['tablefields']);
			$name = $_POST['tablename'];
			echo "<form action='".$thisName."?tablename=".$name."&rows=".$num."' method='post'>";
			echo "<table border='0' cellpadding='2' cellspacing='1'>";
			echo "<tr>";
			echo "<td class='tdheader'>";
			echo "Field";
			echo "</td>";
			echo "<td class='tdheader'>";
			echo "Type";
			echo "</td>";
			echo "<td class='tdheader'>";
			echo "Primary Key";
			echo "</td>";
			echo "<td class='tdheader'>";
			echo "Autoincrement";
			echo "</td>";
			echo "<td class='tdheader'>";
			echo "Not Null";
			echo "</td>";
			echo "<td class='tdheader'>";
			echo "Default Value";
			echo "</td>";
			echo "</tr>";
			for($i=0; $i<$num; $i++)
			{
				echo "<tr>";
				if($i%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<input type='text' name='".$i."_field' style='width:200px;'/>";
				echo "</td>";
				if($i%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<select name='".$i."_type'>";
				echo "<option value='INTEGER' selected='selected'>INTEGER</option>";
				echo "<option value='REAL'>REAL</option>";
				echo "<option value='TEXT'>TEXT</option>";
				echo "<option value='BLOB'>BLOB</option>";
				echo "<option value='NULL'>NULL</option>";
				echo "</select>";
				echo "</td>";
				if($i%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<input type='checkbox' name='".$i."_primarykey'/> Yes";
				echo "</td>";
				if($i%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<input type='checkbox' name='".$i."_autoincrement'/> Yes";
				echo "</td>";
				if($i%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<input type='checkbox' name='".$i."_notnull'/> Yes";
				echo "</td>";
				if($i%2)
					echo "<td class='td1'>";
				else
					echo "<td class='td2'>";
				echo "<input type='text' name='".$i."_defaultvalue' style='width:100px;'/>";
				echo "</td>";
				echo "</tr>";
			}
			echo "</table>";
			if(isset($_POST['addfields']))
				echo "<input type='submit' name='addfieldsconfirm' value='Add Field(s)'/>";
			else
				echo "<input type='submit' name='createtableconfirm' value='Create'/>";
			echo "</form>";
		}
	}
	else if(isset($_GET['table']) && !isset($_POST['rename']))
	{
		if(isset($_GET['view']))
			$view = $_GET['view'];
		else
			$view = "browse";
			
		$table = $_GET['table'];
		echo "<h2>Table: ".$table."</h2>";
		
		if(isset($_GET['delete']) && !isset($_GET['confirm']))
		{
			echo "<div class='confirm'>";
			echo "Are you sure you want to delete row with '".$_GET['pk']."' of '".$_GET['delete']."'?<br/><br/>";
			echo "<a href='".$thisName."?table=".$table."&delete=".$_GET['delete']."&pk=".$_GET['pk']."&confirm=1'>Confirm</a> | ";
			echo "<a href='".$thisName."?table=".$table."'>Cancel</a>";
			echo "</div>";
		}
		else if(isset($_GET['edit']) && !isset($_GET['confirm']))
		{
			echo "<div class='confirm'>";
			$db->generateEdit($table, $_GET['edit'], $_GET['pk']);
			echo "</div>";
		}
		else
		{
			if(isset($_GET['delete']) && isset($_GET['confirm']))
			{
				if($_GET['pk']=="" || $_GET['delete']=="")
					die("Error: GET parameters are blank");
				
				$query = "DELETE FROM ".$table." WHERE ".$_GET['pk']."='".$_GET['delete']."'";
				$result = $db->query($query);
				echo "<div class='confirm'>";
				if($result)
					echo "Row with '".$_GET['pk']."' of '".$_GET['delete']."' has been deleted.";
				else
					echo "An error occured.";
				echo "</div>";
			}
			else if(isset($_GET['edit']) && isset($_GET['confirm']))
			{
				if($_GET['pk']=="" || $_GET['edit']=="")
					die("Error: GET parameters are blank");
				
				$query = "UPDATE ".$table." SET ";
				foreach($_POST as $vblname => $value)
				{
					$query .= $vblname."='".$value."', ";
				}
				$query = substr($query, 0, sizeof($query)-3);
				
				$query .= " WHERE ".$_GET['pk']."='".$_GET['edit']."'";
				
				$result = $db->query($query);
				echo "<div class='confirm'>";
				if($result)
					echo "Row with '".$_GET['pk']."' of '".$_GET['edit']."' has been edited.";
				else
					echo "An error occured.";
				echo "</div>";
			}
			else if(isset($_GET['insert']))
			{	
				echo "<div class='confirm'>";
				echo "A new row has been inserted into '".$_GET['table']."'.";
				echo "</div>";
			}
			echo "<a href='".$thisName."?table=".$table."&view=browse' ";
			if($view=="browse")
				echo "class='tab_pressed'";
			else
				echo "class='tab'";
			echo ">Browse</a>";
			echo "<a href='".$thisName."?table=".$table."&view=structure' ";
			if($view=="structure")
				echo "class='tab_pressed'";
			else
				echo "class='tab'";
			echo ">Structure</a>";
			echo "<a href='".$thisName."?table=".$table."&view=insert' ";
			if($view=="insert")
				echo "class='tab_pressed'";
			else
				echo "class='tab'";
			echo ">Insert</a>";
			echo "<a href='".$thisName."?table=".$table."&view=rename' ";
			if($view=="rename")
				echo "class='tab_pressed'";
			else
				echo "class='tab'";
			echo ">Rename</a>";
			echo "<a href='".$thisName."?emptytable=".$table."' ";
			echo "class='tab' style='color:red;'";
			echo ">Empty</a>";
			echo "<a href='".$thisName."?droptable=".$table."' ";
			echo "class='tab' style='color:red;'";
			echo ">Drop</a>";
			echo "<div style='clear:both;'></div>";
			echo "<div id='main'>";
			
			if($view=="structure")
			{
				$db->generateStructure($table);
			}
			else if($view=="insert")
			{
				$db->generateInsert($table);
			}
			else if($view=="rename")
			{
				$db->generateRename($table);
			}
			else
			{
				if(isset($_POST['startRow']))
					$_SESSION['startRow'] = $_POST['startRow'];
		
				if(isset($_POST['numRows']))
					$_SESSION['numRows'] = $_POST['numRows'];
			
				if(!isset($_SESSION['startRow']))
					$_SESSION['startRow'] = 0;
			
				if(!isset($_SESSION['numRows']))
					$_SESSION['numRows'] = 30;
		
				echo "<form action='".$thisName."?table=".$table."' method='post'>";
				echo "<input type='submit' value='Show : ' name='show'/> ";
				echo "<input type='text' name='numRows' style='width:50px;' value='".$_SESSION['numRows']."'/> ";
				echo "row(s) starting from record # ";
				echo "<input type='text' name='startRow' style='width:90px;' value='".$_SESSION['startRow']."'/>";
				echo "</form>";
				$db->generateView($table, $_SESSION['numRows'], $_SESSION['startRow'], $_GET['sort'], $_GET['order']);
			}
			
			echo "</div>";
		}
	}
	else
	{
		if(isset($_POST['createtableconfirm']))
		{
			echo "<div class='confirm'>";
			echo "Table '".$_GET['tablename']."' has been created.";
			echo "</div>";
		}
		else if(isset($_GET['droptable']) && isset($_GET['confirm']))
		{
			echo "<div class='confirm'>";
			echo "Table '".$_GET['droptable']."' has been dropped.";
			echo "</div>";
		}
		else if(isset($_GET['emptytable']) && isset($_GET['confirm']))
		{
			echo "<div class='confirm'>";
			echo "Table '".$_GET['emptytable']."' has been emptied.";
			echo "</div>";
		}
		
		if(isset($_GET['emptytable']) && !isset($_GET['confirm']))
		{
			echo "<div class='confirm'>";
			echo "Are you sure you want to empty the table '".$_GET['emptytable']."'?<br/><br/>";
			echo "<a href='".$thisName."?emptytable=".$_GET['emptytable']."&confirm=1'>Confirm</a> | ";
			echo "<a href='".$thisName."'>Cancel</a>";
			echo "</div>";
		}
		else if(isset($_GET['droptable']) && !isset($_GET['confirm']))
		{
			echo "<div class='confirm'>";
			echo "Are you sure you want to drop the table '".$_GET['droptable']."'?<br/><br/>";
			echo "<a href='".$thisName."?droptable=".$_GET['droptable']."&confirm=1'>Confirm</a> | ";
			echo "<a href='".$thisName."'>Cancel</a>";
			echo "</div>";
		}
		else
		{
			echo "Welcome to SQLite3Admin, version ".$version."<br/>";
			echo "You are currently managing '".$DBFilename."'. To manage a different database, change the database filename variable at the top of this file.<br/><br/>";
		
			$db->generateTableList();
		}
	
	}
	echo "<br/><br/><a href='http://www.danedesigns.com/SQLite3Admin/' target='_blank' style='font-size:12px;'>SQLite3Admin Project</a>";
	echo "</div>";
	echo "</div>";
	echo "</div>";
	$db->close(); //close the database
}
echo "</body>";
echo "</html>";
