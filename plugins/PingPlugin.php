<?php

require_once __DIR__ . '/../lib/PFBPlugin.class.php';

class PingPlugin extends PFBPlugin
{

    function hook($input)
    {
        if (!isset($input->external_user_name)) {
            if (($input->event == "message") && ($input->content == "!ping")) {
                PHPFlowBot::pushToFlow($this->getOriginFlow(), "@" . PHPFlowBot::getUserById($input->user)->nick . " pong");
            }
        }
    }
}