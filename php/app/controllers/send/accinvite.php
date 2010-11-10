<?php

// called by local user to send an accept invite

$this->activateJsonResponse();
$this->requiresLoggedIn();

// we expect:
// an URL where to deliver to
// a friend access key for the remote user to access our social space
// an id to identify the user in the future
// an acceptmessage (must contain text and a friend access key)
// a remote friend access key to access the remote users social space

// validate
// _____________________________________________________________________________


if(!filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL))
{
  $this->returnJsonStatus(1, 'url not OK');
}
$url = $_POST['url'];
 
if(!ctype_alnum($_POST['friendaccesskey']))
{
  $this->returnJsonStatus(1, 'friendaccesskey not OK');
}
$friendaccesskey = $_POST['friendaccesskey'];

if(!ctype_alnum($_POST['remotefriendaccesskey']))
{
  $this->returnJsonStatus(1, 'remotefriendaccesskey not OK');
}
$remotefriendaccesskey = $_POST['remotefriendaccesskey'];

if(!filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT))
{
  $this->returnJsonStatus(1, 'id not OK');
}
$id = $_POST['id'];

if(!json_decode($_POST['acceptmessage']))
{
  $this->returnJsonStatus(1, 'acceptmessage not OK');
}
$acceptmessage = $_POST['acceptmessage'];


// create a friend in the database
// _____________________________________________________________________________

$db->insertRow('friends', 
  array('id' => $id, 'friendaccesskey' => $friendaccesskey, 'status' => 1));


// send invite to remote
// _____________________________________________________________________________

// send our url, friend access key and invitemessage
$our_url = $db->getValue('url');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url . '/?c=recv&a=accinvite');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
curl_setopt($ch, CURLOPT_POSTFIELDS, array('source' => $our_url, 
  'acceptmessage' => $acceptmessage, 'friendaccesskey' => 
  $remotefriendaccesskey));
$answer = curl_exec($ch);
curl_close ($ch);

$answer = json_decode($answer);
if(!$answer || $answer->status != 0)
{
  $this->returnJsonStatus(1, 'unable to send friend accept invite');
}

$this->returnJsonStatus(0, 'accept invite sent');


