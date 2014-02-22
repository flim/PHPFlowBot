<?php

/**
 * Abstract plugin class
 * Class PFBPlugin
 */
abstract class PFBPlugin
{
    protected $ignoreBot = true;    // Ignore message from bot
    protected $input = null;        // The json decoded received data
    protected $originFlow = null;   // The flow where message comes from

    /**
     * @description method to handle the message
     * @param $input stdClass
     * @return mixed
     */
    abstract function hook($input);

    /**
     * @description method to handle the message ignore bot if need
     * @param $input stdClass
     * @return mixed
     */
    public function run($input) {
        if (!$this->ignoreBot || ($this->ignoreBot && $input->user != EXTERNAL_USER_ID)) {
            $this->hook($input);
        }
    }

    // Getters / Setters
    public function setIgnoreBot($ignoreBot)
    {
        $this->ignoreBot = $ignoreBot;
    }

    public function getIgnoreBot()
    {
        return $this->ignoreBot;
    }

    public function setOriginFlow($originFlow)
    {
        $this->originFlow = $originFlow;
    }

    public function getOriginFlow()
    {
        return $this->originFlow;
    }

    public function getName()
    {
        return get_class($this);
    }
}