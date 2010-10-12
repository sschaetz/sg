<?php

	class MyDB extends SQLite3
	{

		function __construct($db)
		{
			$this->open($db, SQLITE3_OPEN_READWRITE);
		}

    function hasRows($res)
    {
      if($res->fetchArray()) 
      {
        $res->reset();
        return true;
      } 
      else 
      {
        $res->reset();
        return false;
      } 
    }

    function getValue($val)
    {
	    $res = $this->query("SELECT key, value FROM data WHERE key = '" . 
        $val . "';");
      if(!($this->hasRows($res)))
      {
        throw new 
          Exception("Could not get value '" . $val . "' from database.");
      }
      $ret = NULL;
      while ($row = $res->fetchArray(SQLITE3_ASSOC)) 
	    {
		    if($row['key'] == $val)
        {
          $ret = $row['value'];
        }
	    }
      return $ret;
    }

    function getValues($val)
    {
      $c = count($val);
      if($c < 2)
      {
        throw new 
          Exception("Pass an array with at least 2 elements to GetValues");
      }

      $where = "'".$val[0]."'";
      for($i=1; $i<$c; $i++)
      {
        $where .= ",'".$val[$i]."'";
      }
      $res = $this->query("SELECT key, value FROM data WHERE key IN (" . 
        $where . ")");
      if(!($this->hasRows($res)))
      {
        throw new 
          Exception("Could not get values from database, no results returned.");
      }
      $data = array(); 
	    while ($row = $res->fetchArray(SQLITE3_ASSOC)) 
	    {
		    $data[$row['key']] = $row['value'];
	    }
      return data;
    }
    
    function updateValue($key, $val)
    {
      $query = $this->exec("UPDATE data SET value= '" .
        $this->escapeString($val) . "' WHERE key='" . $key . "'");
      if (!$query || $this->changes() != 1) 
      {
        throw new Exception("Could not update value $key.");
      }
      return true;
    }
    
    function insertRow($table, $data)
    {
      $fields = array_shift(array_keys($data));
      $values = "'" . $this->escapeString($data[$fields]) . "'";
      foreach($data as $key => $value)
      {
        $fields .= ', '. $key;
        $values .= ", '". $this->escapeString($value) . "'";
      }
      $query = $this->exec("INSERT INTO " . $table . "(" . $fields . 
        ") VALUES (" . $values . ")");
      if (!$query || $this->changes() != 1) 
      {
        throw new Exception("Could not insert row into table $table.");
      }
      return true;
    }

	}



?>
