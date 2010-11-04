<?php

  include('app/inc/configuration.php');
  $conf = new Configuration();
  exec("rm -rf " . $conf->full_install_directory);
  header('Cache-Control: no-cache, must-revalidate');
  header('Content-type: application/json');
  echo json_encode
  (
    array(
      'status' => 0)
  );

