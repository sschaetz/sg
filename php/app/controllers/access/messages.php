<?php

$this->requiresLoggedIn(1, 'login required to access messages');
$this->activateJsonResponse();

die(json_encode($db->getAll('messages')));
