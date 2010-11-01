<?php

  include('app/inc/configuration.php');

  // some tools for installation _______________________________________________

  function generateRandStr($length)
  {
    $randstr = "";
    for($i=0; $i<$length; $i++)
    {
      $randnum = mt_rand(0,61);

      if($randnum < 10)
      {
        $randstr .= chr($randnum+48);
      }
      else if($randnum < 36)
      {
        $randstr .= chr($randnum+55);
      }
      else
      {
        $randstr .= chr($randnum+61);
      }
    }
    return $randstr;
  }

  // the installation script ___________________________________________________
  $conf = new Configuration();
  
  // ___________________________________________________________________________
  // validate the url, check that it meets format requirements 
  if(!isset($_GET['url']) || !ctype_alnum($_GET['url'])) 
    die(json_encode(array('status' => 1)));
  $url = $_GET['url'];
  // and that it does not exist yet
  if(is_dir($conf->full_install_directory.$url)) 
    die(json_encode(array('status' => 2)));

  // validate the key, check that it meets format requirements 
  if(!isset($_GET[$conf->login_key_name]) || 
    !ctype_alnum($_GET[$conf->login_key_name])) 
    die(json_encode(array('status' => 3)));
  $key = $_GET[$conf->login_key_name];

  // validate the encrsalt, check that it meets format requirements  
  if(!isset($_GET['encrsalt']) || !ctype_alnum($_GET['encrsalt'])) 
    die(json_encode(array('status' => 4)));
  $encrsalt = $_GET['encrsalt'];

  // validate the pswdsalt, check that it meets format requirements  
  if(!isset($_GET['pswdsalt']) || !ctype_alnum($_GET['pswdsalt'])) 
    die(json_encode(array('status' => 5)));
  $pswdsalt = $_GET['pswdsalt'];

  // ___________________________________________________________________________ 

  // create the url directory
  mkdir($conf->full_install_directory.$url);

  // copy the index and the database file
  copy('app/index.php', $conf->full_install_directory.$url.'/index.php');
  copy('app/db.sql3', $conf->full_install_directory.$url.'/db.sql3');
  chmod($conf->full_install_directory.$url.'/db.sql3', 0666);

  // and written to the database
  $db = new SQLite3($conf->full_install_directory.$url.'/db.sql3', 
    SQLITE3_OPEN_READWRITE);

	$db->exec("INSERT INTO data (key, value) VALUES('url', '".
    $url."');");
	$db->exec("INSERT INTO data (key, value) VALUES('encrsalt', '".
    $encrsalt."');");
  $db->exec("INSERT INTO data (key, value) VALUES('pswdsalt', '".
    $pswdsalt."');");
  $db->exec("INSERT INTO data (key, value) VALUES('".$conf->login_key_name.
    "', '".$key."');");
  $db->exec("INSERT INTO data (key, value) VALUES('data', '');");
  //echo("putting $key in the database");
  // the writekey should also be returned once
  echo json_encode(
    array(
      'status' => 0, 
      'message' => 'insatllation complete',
      'writekey' => $key,
    	'pswdsalt' => $pswdsalt
    )
  );

?>
