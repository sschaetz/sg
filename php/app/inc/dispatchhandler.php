<?php

  require_once('configuration.php');
  require_once('accesscontrolhandler.php');
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
    private $friendAccess;
    private $jsonHeaderSet;
    private $dbh;  	

    /**
     * @brief Constructor creates a configuration object and starts dispatching 
     */
    public function __construct(Configuration $conf)
    {
    	$this->conf = $conf;
    	$this->dispatch();
      $this->jsonHeaderSet = false;
    }
    
    /**
     * @brief Require Login
     *
     * A method for actions to require the user to be logged in for execution.
     * If the user is not logged in, this method stops script execution.
     *
     * If called with any parameters (1 or 2) returns JSON object.
     *
     * @param Status int optional status number (enforces JSON return)
     *
     * @param Message string optional message (enforces JSON return)
     *
     * @returns nothing
     */
    private function requiresLoggedIn()
    {
      if(!$this->loggedIn)
      {
        $dieValue = 'Sorry, login required.';
        if(func_num_args() == 1)
        {
          $this->activateJsonResponse();
          $dieValue = json_encode(array('status' => func_get_arg(0)));
        }
        else if(func_num_args() == 2)
        {
          $this->activateJsonResponse();
          $dieValue = json_encode(array('status' => func_get_arg(0),
            'message' => func_get_arg(1)));
        }
        die($dieValue);
      }
    }
    
    /**
     * @brief Require Access Key
     *
     * A method for actions called by a remote user that require an access key
     * to identify the user. 
     *
     * @param Status int optional status number (enforces JSON return)
     *
     * @param Message string optional message (enforces JSON return)
     *
     * @returns nothing
     */
    private function requiresAccessKey()
    {
      if(!$this->friendAccess)
      {
        $dieValue = 'Sorry, access key required.';
        if(func_num_args() == 1)
        {
          $this->activateJsonResponse();
          $dieValue = json_encode(array('status' => func_get_arg(0)));
        }
        else if(func_num_args() == 2)
        {
          $this->activateJsonResponse();
          $dieValue = json_encode(array('status' => func_get_arg(0),
            'message' => func_get_arg(1)));
        }
        die($dieValue);
      }
    }

    /**
     * @brief Activate JSON response
     *
     * A method for actions to activate json reponse (sets json header)
     *
     * @returns nothing
     */
    private function activateJsonResponse()
    {
      if(!$this->jsonHeaderSet)
      {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        $this->jsonHeaderSet = true;
      }
    }

    /**
     * @brief Emit JSON status message and quit execution
     *
     * A method for actions to emit a JSON status message
     *
     * @param st int status number
     *
     * @param msg string optional message
     *
     * @returns nothing
     */
    private function returnJsonStatus($st, $msg = '')
    {
      $this->activateJsonResponse();
      die(json_encode(array('status' => $st, 'message' => $msg)));
    }
    
    /**
     * @brief Dispatch method
     * 
     * Analyzes the controller (c) and action (a) get parameters and tries
     * to call this controller-action combination. It checks if it exists
     * by evaluating if the controller folder and action.php file inside exist.
     * It calls the action simply by including the file.
     * It makes the $db and $sh variables available to the action (database 
     * handler and session handler).
     *
     * The controller is optional, if no controller is specified, the default
     * controller is used.
     *
     * If the controller-action combination does not exist it calls the default
     * controller and action (can specified in the configuration).
     *
     * This method makes all private members of the dispatch class available to
     * the action.
     *
     * @returns nothing
     */
    private function dispatch()
    {
    	                // get the controler and the action and check if it exists
    	$action = $this->conf->full_controller_directory . 
        $this->conf->default_controller . '/' . 
        $this->conf->default_action. '.php';

    	$action_tmp = '';
    	if(isset($_GET['c']) && ctype_alpha($_GET['c']) && 
    	  is_dir($this->conf->full_controller_directory . $_GET['c']))
    	{
    		$action_tmp = $this->conf->full_controller_directory . $_GET['c'];
    	}
      else
      {
        $action_tmp = $this->conf->full_controller_directory . 
          $this->conf->default_controller;
      }
    	
      if(isset($_GET['a']) && ctype_alpha($_GET['a']) && 
        file_exists($action_tmp . '/' . $_GET['a'] . '.php'))
      {
        $action = $action_tmp . '/' . $_GET['a'] . '.php';
      }
      
      $db = new DBhandler($this->conf->dbconnectionstring);
      $ah = new Accesscontrolhandler($db, $this->conf);
      $conf = $this->conf;               // make conf object available to action
      $this->loggedIn = $ah->loggedIn();
      $this->friendAccess = $ah->friendAccess();
      require($action);
      exit();
    }

  }
