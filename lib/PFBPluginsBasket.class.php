<?php

require_once __DIR__ . '/PFBBasePluginsBasket.class.php';
require_once __DIR__ . '/../plugins/PingPlugin.php';
require_once __DIR__ . '/../plugins/TestPlugin.php';
require_once __DIR__ . '/../plugins/ImAlivePlugin.php';

class PFBPluginsBasket extends PFBBasePluginsBasket
{
    protected function registerPlugins()
    {
        $this->plugins = array(
            new PingPlugin(),
            new TestPlugin(),
            new ImAlivePlugin(),
        );
    }
}