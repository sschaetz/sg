<?php

  require_once('configuration.php');
  require_once('sessionhandler.php');
  require_once('dbhandler.php');

  /**
   * Dispatchhandler
   * 
   * dispatch control
   * 
   * @author seb
   *
   */

  class Dispatchhandler
  {
    private $conf;
    private $loggedIn;
  	
    public function __construct(Configuration $conf)
    {
    	$this->conf = $conf;
    	$this->dispatch();
    }
    
    private function requireLoggedIn()
    {
    	if(!$this->loggedIn)
    	{
    		die('login required');
    	}
    }
    
    private function dispatch()
    {
    	                // get the controler and the action and check if it exists
    	$action = $this->conf->full_controller_directory . 'default/default.php';
    	$action_tmp = '';
    	if(isset($_GET['c']) && ctype_alpha($_GET['c']) && 
    	  is_dir($this->conf->full_controller_directory . $_GET['c']))
    	{
    		$action_tmp = $this->conf->full_controller_directory . $_GET['c'];
    	}
    	
      if(isset($_GET['a']) && ctype_alpha($_GET['a']) && 
        file_exists($action_tmp . '/' . $_GET['a'] . '.php'))
      {
        $action = $action_tmp . '/' . $_GET['a'] . '.php';
      }
      
      $db = new DBhandler($this->conf->dbconnectionstring);
      $sh = new Sessionhandler($db, $this->conf);
      $this->loggedin = $sh->loggedIn();
      require($action);
    }

  }
