<?php

// called by remote user to deliver an invite

// we expect:
// a URL of the sender
// a message (containing a friend access key the local user to access the remote 
// user social space)

// validate
// _____________________________________________________________________________


if(!filter_input(INPUT_POST, 'source', FILTER_VALIDATE_URL))
{
  $this->returnJsonStatus(1, 'source not OK');
}
$source = $_POST['source'];

if(!json_decode($_POST['invitemessage']))
{
  $this->returnJsonStatus(1, 'invitemessage not OK');
}
$invitemessage = $_POST['invitemessage'];

// save message
// _____________________________________________________________________________

// status is optional for later use (spam prevention etc)
$db->insertRow('invites', 
  array('source' => $source, 'invitemessage' => $invitemessage, 'status' => 0,
  'timestamp' => 0));

$this->returnJsonStatus(0, 'invite Ok');


