<?php

  include('app/inc/configuration.php');
  $conf = new Configuration();
  exec("rm -rf " . $conf->full_install_directory);
  echo json_encode
  (
    array(
      'status' => 0)
  );
