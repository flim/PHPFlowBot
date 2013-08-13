# PHPFlow

Flowdock API Library in PHP

## Require
PHP Version 5.3.10

## Installation
Install composer in the project
```BASH
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```
or you can just remove the require line from the './PHPFlowBot.php'
```PHP
require_once __DIR__ . '/vendor/autoload.php';
```
## Configuration
Rename './config/config.php.sample' to './config/config.php'
and configure it by setting your USER_TOKEN.

Rename './lib/PFBFlowRegister.class.php.sample' to ./lib/PFBFlowRegister.class.php'
and configure it by adding all channels (flows) you want to use.

Open the file './PHPFlowBot.php'
```PHP
// We have to call init before using the class.
PHPFlowBot::init();

// We run the bot by giving the list of flow we want to listen to.
// We can specify the flow by giving the customName setted in the class 'PFBFlowRegister'.
PHPFlowBot::streamFlow(PFBFlowRegister::$flows);
```

## Run it
```BASH
php ./PHPFlowBot.php
```
Enjoy!

## Plugins
All plugins are in the './plugins' folder.

### Create a plugin
To write your own plugin, create class in the './plugins' folder.
Your plugin have to extends the abstract class 'PFBPlugin' from the './lib' folder.
Then implement the 'hook' method which takes one parameter '$input'.
'$input' is the decoded json message sent by the Flowdock API.

```PHP
require_once __DIR__.'/../lib/PFBPlugin.class.php';

class PingPlugin extends PFBPlugin {

    function hook($input)
    {
        if (!isset($input->external_user_name)) {
            if (($input->event == "message") && ($input->content == "!ping")) {
                PHPFlowBot::pushToFlow($this->getOriginFlow(), "@" . PHPFlowBot::getUserById($input->user)->nick . " pong");
            }
        }
    }
}
```
### Enable plugins
To enable plugins, open the file './lib/PFBPluginsBasket.class.php'.
Then add the dependency to your plugin class then instantiate your plugin as a new entry in $this->plugins.
```PHP
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
```