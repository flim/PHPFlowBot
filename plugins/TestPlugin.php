<?php

require_once __DIR__ . '/../lib/PFBPlugin.class.php';

class TestPlugin extends PFBPlugin
{

    function hook($input)
    {
        if (!isset($input->external_user_name)) {
            if (($input->event == "message") && ($input->content == "!test")) {
                PHPFlowBot::pushToFlow($this->getOriginFlow(), "test réussi");
            }
        }
    }
}