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
    die_status(12);
  }
  
  function save_html($db, $session)
  {
    die('Error, status 12');
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
    die('Error, status 12');
  }
   
 
  //  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _

  
  function send($db, $session)
  {
    die_status(13);
  }
  
  function send_html($db, $session)
  {
    die('Error, status 13');
  }

  /**
   * action called if local user wants to send a message, messages can be
   * normal (send a regular encrypted message)
   * invite (send an invite to another user)
   * accept (notify another user that an invite was accepted)
   * 
   * @param $db
   * @param $session
   */
  function send_loggedin($db, $session)
  {
    // each send command must have a type to specify which message type
    // should be sent, check if type is set and if it is correct and dispatch
    // action from there
    
    if(isset($_POST['type']) && ctype_alnum($_POST['type']))
    {
      $type = $_POST['type'];
      if($type == 0)
      {
        send_normal_message($url, $message, $key, $db);
        return;
      }
      else if($type == 1)
      {
        send_invite_message($url, $message, $db);
        return;
      }
      else if($type == 2)
      {
        send_accept_message($url, $message, $db);
        return;
      }
    }
    
    // error
    die_status(13);

  }
  
  function send_html_loggedin($db, $session)
  {
    die('Error, status 13');
  }
   
 
  //  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _  _

  
  function recv($db, $session)
  {
    die_status(1);
  }
  
  function recv_html($db, $session)
  {
    die('Error, status 14');
  }

  /**
   * action called if remote user wants to deliver a message, messages can be
   * normal (receive a regular encrypted message)
   * invite (receive an invite to another user)
   * accept (receive notification from another user that an invite was accepted)
   * 
   * @param $db
   * @param $session
   */
  function recv_loggedin($db, $session)
  {
    // each send command must have a type to specify which message type
    // should be sent, check if type is set and if it is correct and dispatch
    // action from there
    
    if(isset($_POST['type']) && ctype_alnum($_POST['type']))
    {
      $type = $_POST['type'];
      if($type == 0)
      {
        recv_normal_message($url, $message, $key, $db);
        return;
      }
      else if($type == 1)
      {
        recv_invite_message($url, $message, $db);
        return;
      }
      else if($type == 2)
      {
        recv_accept_message($url, $message, $db);
        return;
      }
    }
    
    // error
    die_status(14);

  }
  
  function recv_html_loggedin($db, $session)
  {
    die('Error, status 14');
  }
  
  
  // ___________________________________________________________________________
  // ___________________________________________________________________________
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

  //dispatch
  call_user_func($functionname, $db, $session);

  
/*
                                                               // validate data
  $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
  $accesskey = filter_var($_POST['accesskey'], FILTER_VALIDATE_INT, 
    FILTER_FLAG_ALLOW_HEX);
  $encryptkey = filter_var($_POST['encryptkey'], FILTER_VALIDATE_INT);
  $url = filter_var($_POST['url'], FILTER_VALIDATE_URL);
  $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
  if(!$id || !$accesskey || !$encryptkey || !$url || !$message)
  {
    die_status(13);
  }
  
           // add access key to friends table to allow remote to send messages
  $data = array('id' => $id, 'accesskey' => $accesskey, 'status' => 1);
  $db->insertRow('friends', $data);
  
                                         // send the invite to the remote user
  $ch = curl_init();   
  curl_setopt($ch, CURLOPT_URL, $url . '?do=receiveinvite'); 
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, 'accesskey=' . $accesskey . 
    '&encryptkey=' . $encryptkey . '&message=' . $message);
  curl_exec($ch);    
  $ret = json_decode(curl_close($ch));
  if($ret->status != 0)
  {
    throw new Exception("Could not send the invite to the remote user.");
  }
  die_status(0);
*/ 