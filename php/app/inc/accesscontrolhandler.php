<?php
  
  require_once('dbhandler.php');
  require_once('configuration.php');

  /**
   * Sessionhandler
   * 
   * user authentication and session control
   * 
   * @author seb
   *
   */
  class Accesscontrolhandler
	{
    private $loggedIn;
    private $friendAccess;
    private $friendAccessId;
    private $keyname;
    private $friendacceskeyname;

    /**
     * Constructor tries to create or pick up a new session
     * If not possible it checks if a remote session by a friend is requested
     * @param $db Database connection
     * @return nothing
     */
    public function __construct(DBhandler $db, Configuration $conf)
		{                
		  session_start();
      $this->loggedIn = false;
      $this->friendAccess = false;
      $this->friendAccessId = -1;
      $this->keyname = $conf->login_key_name;
      $this->friendacceskeyname = $conf->friend_access_key_name;
      
      // check user login and friend access
      $this->checkUserLoggedIn($db);
      $this->checkFriendAccess($db);
		}
  
    /**
     * Check if user is logged in and set member in objects accordingly
     * @param $db Database connection
     * @return nothing
     */
    private function checkUserLoggedIn(DBhandler $db)
    {
      $key = '';
      if(isset($_POST[$this->keyname]))           // check if key is set in POST
      {
        $key = $_POST[$this->keyname];
      }
      else if(isset($_GET[$this->keyname]))        // check if key is set in GET
      {
        $key = $_GET[$this->keyname];
      }
      else if(isset($_SESSION[$this->keyname]))   // check if key is set in sess
      {
        $key = $_SESSION[$this->keyname];
      }
      else                                              // key is not set at all
      {
        session_destroy();
      }
      if($key != '')         // if a key was found in the request or the session
      {
	      $dbkey = $db->getValue($this->keyname);
	      if($dbkey == $key)
	      {
	        $this->loggedIn = true;
	        $_SESSION[$this->keyname] = $key;
	      }
      }
    }

    /**
     * Check if friend accesses site using his key and set member in objects 
     * accordingly
     * @param $db Database connection
     * @return nothing
     */
    private function checkFriendAccess(DBhandler $db)
    {
      $access = false;
      if(isset($_POST['friendaccesskey']))
      {
        $access = $db->checkFriendAccess($_POST['friendaccesskey']);
      } 
      else if(isset($_GET['friendaccesskey']))
      {
        $access = $db->checkFriendAccess($_GET['friendaccesskey']);
      }
      if($access)
      {
        $this->friendAccess = true;
        $this->friendAccessId = $access;
      }
    }

		/**
		 * Returns if user is logged in
		 * @return bool
		 */
    public function loggedIn()
    {
      return $this->loggedIn;
    }

		/**
		 * Returns if friend accesses page with access key
		 * @return bool
		 */
    public function friendAccess()
    {
      if($this->friendAccess)
      {
        return $this->friendAccessId;
      }
      else
      {
        return false;
      }    
    }

    /**
     * Destroys the session if the user is logged in (if a session was created)
     * @return nothing
     */
    public function destroy()
    {
      if($this->loggedIn)
      {
        session_destroy();
      }
      return;
    }

	}
