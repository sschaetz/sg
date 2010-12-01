<?php

  include('app/inc/configuration.php');

  $username = '';
  if(isset($_GET['username']))
  {
    $username = $_GET['username'];
  }  

  $conf = new Configuration();
  exec("rm -rf " . $conf->full_install_directory . $username);
  header('Cache-Control: no-cache, must-revalidate');
  header('Content-type: application/json');
  echo json_encode
  (
    array(
      'status' => 0)
  );

