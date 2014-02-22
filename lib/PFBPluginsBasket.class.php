<?php

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