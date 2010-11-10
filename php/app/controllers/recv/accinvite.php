<?php

// called by remote user to deliver an accept invite

$this->requiresAccessKey();

// we expect:
// a URL of the sender
// an acceptmessage
// access key to our social space

// validate
// _____________________________________________________________________________


if(!filter_input(INPUT_POST, 'source', FILTER_VALIDATE_URL))
{
  $this->returnJsonStatus(1, 'source not OK');
}
$source = $_POST['source'];

if(!json_decode($_POST['acceptmessage']))
{
  $this->returnJsonStatus(1, 'acceptmessage not OK');
}
$invitemessage = $_POST['acceptmessage'];

// save message
// _____________________________________________________________________________

// status is optional for later use (spam prevention etc)
$db->insertRow('invites', 
  array('source' => $source, 'invitemessage' => $invitemessage, 'status' => 1,
  'timestamp' => 0));

$this->returnJsonStatus(0, 'invite Ok');


