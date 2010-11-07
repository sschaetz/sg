<?php

// called by remote user to deliver an invite

// we expect:
// a URL of the sender
// a friend access key the local user to access the remote user social space
// a message

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
$message = $_POST['invitemessage'];

// save message
// _____________________________________________________________________________


$this->returnJsonStatus(0, 'invite Ok');


