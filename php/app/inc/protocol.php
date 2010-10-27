<?php

function deliver($url, $postfields)
{
  $ch = curl_init();   
  curl_setopt($ch, CURLOPT_URL, $url . '?do=ep'); 
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
  curl_exec($ch);    
  return $ret = json_decode(curl_close($ch));
}


/**
 * Recv a normal encrypted message
 * @param unknown_type $url
 * @param unknown_type $message
 * @param unknown_type $key
 * @param unknown_type $db
 */
function recv_normal_message($url, $message, $key, $db)
{
  
}

/**
 * Recv an invite message
 * @param unknown_type $url
 * @param unknown_type $message
 * @param unknown_type $db
 */
function recv_invite_message($url, $message, $db)
{
  
}

/**
 * Recv an accept message
 * @param unknown_type $url
 * @param unknown_type $message
 * @param unknown_type $db
 */
function recv_accept_message($url, $message, $db)
{
  
}
