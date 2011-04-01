<?php

$this->requiresLoggedIn(1, 'login required to access invites');
$this->activateJsonResponse();

die(json_encode($db->getAll('invites')));
