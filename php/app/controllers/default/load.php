<?php

$this->requiresLoggedIn(1, 'loggin required to load data');
$this->activateJsonResponse();

$data = $db->getValue($conf->data_block_name);
die(json_encode(array('status' => 0, 'data' => $data)));

