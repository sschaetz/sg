<?php

// called by local user to send a message

$this->activateJsonResponse();
$this->requiresLoggedIn();

// we expect:
// an URL where to deliver to
// a remote friend access key to access the social space of the remote user
// a messagetext


// validate
// _____________________________________________________________________________


if(!filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL))
{
  $this->returnJsonStatus(1, 'url not OK');
}
$url = $_POST['url'];
 
if(!ctype_alnum($_POST['remotefriendaccesskey']))
{
  $this->returnJsonStatus(1, 'remotefriendaccesskey not OK');
}
$remotefriendaccesskey = $_POST['remotefriendaccesskey'];

if(!json_decode($_POST['messagetext']))
{
  $this->returnJsonStatus(1, 'messagetext not OK');
}
$messagetext = $_POST['messagetext'];


// send invite to remote
// _____________________________________________________________________________

// send our url and invitemessage (containing friend access key)
$our_url = $db->getValue('url');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url . '/?c=recv&a=message');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
curl_setopt($ch, CURLOPT_POSTFIELDS, array('source' => $our_url, 
    'messagetext' => $messagetext,
    'friendaccesskey' => $remotefriendaccesskey));
$answer = curl_exec($ch);
curl_close ($ch);

$answer = json_decode($answer);
if(!$answer || $answer->status != 0)
{
  $this->returnJsonStatus(1, 'unable to send friend message');
}

$this->returnJsonStatus(0, 'message sent');


