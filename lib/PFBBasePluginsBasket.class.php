<?php

abstract class PFBBasePluginsBasket
{
    protected $plugins;

    public function PFBBasePluginsBasket() {
        $this->loadPlugins();
    }

    /**
     * @return array|bool return the array of plugin on success on False
     */
    public function loadPlugins()
    {
        $this->registerPlugins();
        if (is_array($this->plugins)) {
            return $this->plugins;
        }
        return false;
    }

    protected abstract function registerPlugins();
}