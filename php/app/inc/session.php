<?php

  // initiate or reactivate session
  class MySession
	{
    private $loggedIn;

    public function __construct($db)
		{                                        
      $this->loggedIn = false;
      session_start();
      $key = '';
                                                           // get the input data 
      if(isset($_GET['key']))
      {
        $key = $_GET['key'];
      }
      else if(isset($_SESSION['key']))
      {
        $key = $_SESSION['key'];
      }
      else
      {
        session_destroy();
        return;
      }
      $dbkey = $db->getValue('key');
      if($dbkey == $key)
      {
        $this->loggedIn = true;
        $_SESSION['key'] = $key;
        return;
      }
		}
    
    public function loggedIn()
    {
      return $this->loggedIn;
    }

    public function destroy()
    {
      session_destroy();
    }

	}


?>
