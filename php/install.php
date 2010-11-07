<?php

  include('app/inc/configuration.php');

  // the installation script ___________________________________________________
  $conf = new Configuration();

  // always returns JSON format
  header('Cache-Control: no-cache, must-revalidate');
  header('Content-type: application/json');
  
  // ___________________________________________________________________________
  // validate the url, check that it meets format requirements 
  if(!isset($_GET['url']) || !ctype_alnum($_GET['url'])) 
    die(json_encode(array('status' => 1)));
  $url = $_GET['url'];
  // and that it does not exist yet
  if(is_dir($conf->full_install_directory.$url)) 
    die(json_encode(array('status' => 2)));

  // validate the key, check that it meets format requirements 
  if(!isset($_GET[$conf->login_key_name]) || 
    !ctype_alnum($_GET[$conf->login_key_name])) 
    die(json_encode(array('status' => 3)));
  $loginkey = $_GET[$conf->login_key_name];

  // validate the encrsalt, check that it meets format requirements  
  if(!isset($_GET[$conf->encrypt_key_salt_name]) || 
    !ctype_alnum($_GET[$conf->encrypt_key_salt_name])) 
    die(json_encode(array('status' => 4)));
  $encryptkeysalt = $_GET['encryptkeysalt'];

  // validate the pswdsalt, check that it meets format requirements  
  if(!isset($_GET[$conf->login_key_salt_name]) || 
    !ctype_alnum($_GET[$conf->login_key_salt_name])) 
    die(json_encode(array('status' => 5)));
  $loginkeysalt = $_GET['loginkeysalt'];

  // ___________________________________________________________________________ 

  // create the url directory
  mkdir($conf->full_install_directory.$url);

  // copy the index and the database file
  copy('app/index.php', $conf->full_install_directory.$url.'/index.php');
  copy('app/db.sql3', $conf->full_install_directory.$url.'/db.sql3');
  chmod($conf->full_install_directory.$url.'/db.sql3', 0666);

  // and written to the database
  $db = new SQLite3($conf->full_install_directory.$url.'/db.sql3', 
    SQLITE3_OPEN_READWRITE);

	$db->exec("INSERT INTO data (key, value) VALUES('url', '".
    $conf->user_url_base . $url."');");
	$db->exec("INSERT INTO data (key, value) VALUES('".
    $conf->encrypt_key_salt_name."', '".$encryptkeysalt."');");
  $db->exec("INSERT INTO data (key, value) VALUES('".
    $conf->login_key_salt_name."', '".$loginkeysalt."');");
  $db->exec("INSERT INTO data (key, value) VALUES('".
    $conf->login_key_name."', '".$loginkey."');");
  $db->exec("INSERT INTO data (key, value) VALUES('".
    $conf->data_block_name."', '');");

  // the writekey should also be returned once
  echo json_encode(
    array(
      'status' => 0, 
      'message' => 'insatllation complete'
    )
  );

?>
