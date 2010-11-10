<?php

  /**
   * DBhandler
   * 
   * database access abstraction
   * 
   * @author seb
   *
   */
  class DBhandler
	{
		private $dbh;

		/**
		 * Constructor create a connection to a database
		 * @param $pdoconnectstring PDO database connection string
		 * @return nothing
		 */
		function __construct($pdoconnectstring)
		{
      $this->dbh = new PDO($pdoconnectstring);
		}

		/**
		 * Get a value from the data table 
		 * @param $key the key of the data 
		 * @return the value
		 */
    function getValue($key)
    {
      $stmt = 
        $this->dbh->prepare("SELECT key, value FROM data WHERE key = :key;");
	    $stmt->bindParam(':key', $key, PDO::PARAM_STR);
      $stmt->execute();
    	
      $data = $stmt->fetchAll();
      if(count($data) < 1 || $data[0]['key'] != $key)
      {
        throw new 
          Exception("Could not get value '" . $val . "' from database.");
      }
      return $data[0]['value'];
    }

    /**
     * Get values from the data table
     * @param $keys the keys of the data table
     * @return associative array (key=>value)
     */
    function getValues($keys)
    {
    	                     // create a placeholder for each element of the array
			$placeholders = implode(',', array_fill(0, count($keys), '?'));
			$stmt = $this->dbh->prepare(
			  "SELECT key, value FROM data WHERE key IN (" . $placeholders . ");");
			$stmt->execute($keys);
      $data = $stmt->fetchAll();
			if(count($data) != count($keys))
			{
        throw new 
          Exception("Could not get requested values from database.");
			}
			$retvalue = array();
      foreach($data as $row)
      {
      	$retvalue[$row['key']] = $row['value'];
      }
      return $retvalue;
    }

    /**
     * Get all contents from a table
     * @param $table the name of the table
     * @return associative array (row1=>(key1=>value1,key2=>value2,...),row2..)
     */
    function getAll($table)
    {
    	                     // create a placeholder for each element of the array
			$stmt = $this->dbh->prepare("SELECT * FROM ".$table);
			$stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update a value in the data table
     * @param $key the key of the value to update
     * @param $val the new value
     * @return true on success
     */
    function updateValue($key, $val)
    {
      $stmt = 
        $this->dbh->prepare("UPDATE data SET value = :value WHERE key = :key");
      $stmt->bindParam(':value', $val, PDO::PARAM_STR);
      $stmt->bindParam(':key', $key, PDO::PARAM_STR);
      $stmt->execute();
    	
      if($stmt->rowCount() != 1) 
      {
        throw new Exception("Could not update value $key.");
      }
      return true;
    }
    
    /**
     * Insert a row into any table
     * @param $table name of the table
     * @param $data associative array of data that should be inserted
     * @return true on success
     */
    function insertRow($table, $data)
    {
      $fields = implode(',', array_keys($data));
      $placeholders = implode(',', array_fill(0, count($data), '?'));
      $stmt = $this->dbh->prepare("INSERT INTO " . $table .
        " (" . $fields . ") VALUES (" . $placeholders . ")");

      $data = array_values($data);
      $stmt->execute($data);
      
      if($stmt->rowCount() != 1) 
      {
        throw new Exception("Could not insert row into table $table.");
      }
    	return true;
    }
    
    // —————————————————————————————————————————————————————————————————————————
    // Specialized functions, they contain business logic and should not be
    // members of this class but some sort of helper classes for different tasks
    // —————————————————————————————————————————————————————————————————————————
    
    /**
     * Check if a friend has access (if he can send messages)
     * @param $friendaccesskey key friend uses to access this location
     * @return true if friend has access, else false
     */
    function checkFriendAccess($friendaccesskey)
    {
      $stmt =  $this->dbh->prepare("SELECT id from friends " . 
        " WHERE friendaccesskey = :friendaccesskey AND status = 1");
      $stmt->bindParam(':friendaccesskey', $friendaccesskey, PDO::PARAM_STR);
      $stmt->execute();
    	
      $data = $stmt->fetchAll();
      if(count($data) < 1)
      {
        return false;
      }
      else
      {
        return $data[0]['id'];
      }
    }

	}
