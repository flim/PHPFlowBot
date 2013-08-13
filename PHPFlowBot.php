<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/PFBFlowRegister.class.php';
require_once __DIR__ . '/lib/PHPFlowBot.class.php';

PHPFlowBot::init();

PHPFlowBot::streamFlow(PFBFlowRegister::$flows);

?>