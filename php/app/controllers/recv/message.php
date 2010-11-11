<?php

// called by remote user to deliver an accept invite
error_log('hi! '. $_POST['friendaccesskey']);
$this->requiresAccessKey();

// we expect:
// a URL of the sender
// a messagetext
// access key to our social space

// validate
// _____________________________________________________________________________
error_log('hi!');

if(!filter_input(INPUT_POST, 'source', FILTER_VALIDATE_URL))
{
  $this->returnJsonStatus(1, 'source not OK');
}
$source = $_POST['source'];

if(!json_decode($_POST['messagetext']))
{
  $this->returnJsonStatus(1, 'messagetext not OK');
}
$messagetext = $_POST['messagetext'];

// save message
// _____________________________________________________________________________

// status is optional for later use (spam prevention etc)
$db->insertRow('messages', 
  array('source' => $source, 'message' => $messagetext, 
  'friendid' => $ah->friendAccess(), 'timestamp' => 0));

$this->returnJsonStatus(0, 'invite Ok');


