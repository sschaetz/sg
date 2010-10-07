<?php

  include('app/inc/config.php');

  exec("rm -rf " . $install_directory);
  echo json_encode
  (
    array(
      'status' => 0)
  );

?>
