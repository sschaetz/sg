<?php

  /**
   * This is the main backend file
   */

  include('../../php/app/inc/config.php');
  include('../../php/app/inc/db.php');
  include('../../php/app/inc/session.php');

  // helper functions
  // ___________________________________________________________________________
  
  function die_status($status)
  {
    die(json_encode(array('status' => $status)));
  }

  // output functions
  // ___________________________________________________________________________

  
  function def($db, $session)
  {
    $data = array();
    $data['pswdsalt'] = $db->getValue('pswdsalt');
    $data['status'] = 0;
    die(json_encode($data));
  }
  
  function def_html($db, $session)
  {
    $url = $db->getValue('url');
    die("<h3>Social Network space of $url</h3>");
  }

  function def_loggedin($db, $session)
  {
    global $unset_value;
    $data = array();
    $data['data'] = $db->getValue('data');
    $data['encrsalt'] = $db->getValue('encrsalt');
    $data['status'] = 0;
	  die(json_encode($data));
  }
  
  function def_html_loggedin($db, $session)
  {
    def_loggedin($db, $session);
  }

  //  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _

  function logout($db, $session)
  {
    $session->destroy();
    die_status(0);
  }
  
  function logout_html($db, $session)
  {
    $session->destroy();
    echo("Logged out.");
  }
  
  function logout_loggedin($db, $session)
  {
    logout($db, $session);
  }
  
  function logout_html_loggedin($db, $session)
  {
    logout_html($db, $session);
  }
 
 
  //  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _

  function save($db, $session)
  {
    die('nope save');
  }
  
  function save_html($db, $session)
  {
    die('nope save_html');
  }
  
  function save_loggedin($db, $session)
  {
    if(!isset($_POST['data']))                           // check if data is set
    {
       die_status(11);
    }
    $db->updateValue('data', $_POST['data']);
    die_status('0');
  }
  
  function save_html_loggedin($db, $session)
  {
    die('nope save_html_loggedin');
  }
  
  // ___________________________________________________________________________


  // the do variable controls the path that should be taken, if it is not set
  // the default value is assigned
  
  if(!isset($_GET['do']) || !ctype_alnum($_GET['do']))
  {
    $_GET['do'] = 'def';
  }
  
  $json = false;
  if((isset($_GET['json']) && $_GET['json'] == '1') ||
    (isset($_POST['json']) && $_POST['json'] == '1'))
  {
    $json = true;
  }

  // create database class
  $db = new MyDB($dbfilename);
  
  // create session class
  $session = new MySession($db);
  
  
  // build the function name and dispatch it
  $functionname = $_GET['do'];
  if(!$json)
  {
    $functionname .= '_html';
  }
  if($session->loggedIn())
  {
    $functionname .= '_loggedin';
  }
  
  if(!is_callable($functionname))
  {
    echo json_encode(array('status' => 10));
    throw new Exception("Function $functionname does not exist.");
  }

  call_user_func($functionname, $db, $session);

  
?>
