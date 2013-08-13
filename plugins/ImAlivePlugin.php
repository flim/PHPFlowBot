<?php

require_once __DIR__ . '/../lib/PFBPlugin.class.php';

class ImAlivePlugin extends PFBPlugin
{

    function hook($input)
    {
        if (!isset($input->external_user_name)) {
            if (($input->event == "message") && (strstr(strtolower($input->content), "are you alive") !== false)) {
                PHPFlowBot::pushToFlow($this->getOriginFlow(), "I'm alive! \\o/");
            }
        }
    }
}