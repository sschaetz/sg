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
  class Sessionhandler
	{
    private $loggedIn;
    private $keyname;

    /**
     * Constructor tries to create or pick up a new session
     * @param $db Database connection
     * @return nothing
     */
    public function __construct(DBhandler $db, Configuration $conf)
		{                
		  session_start();
      $this->loggedIn = false;
      $this->keyname = $conf->login_key_name;
      $key = '';

      if(isset($_POST[$this->keyname]))           // check if key is set in requ
      {
        $key = $_POST[$this->keyname];
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
	        return;
	      }
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
