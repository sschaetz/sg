<?php

$this->requiresLoggedIn(1, 'login required to save data');
$this->activateJsonResponse();

// we expect the data to be saved received as a single POST parameter
if(!isset($_POST['data']))
{
  $this->returnJsonStatus(1, 'no data transmitted');
}

$db->updateValue($conf->data_block_name, $_POST['data']);
$this->returnJsonStatus(0, 'data saved');

