<?php

require_once __DIR__ . '/vendor/autoload.php';

PHPFlowBot::init();

PHPFlowBot::streamFlow(PFBFlowRegister::$flows);

?>