<?php

require_once __DIR__ . '/../config/config.php';

class PHPFlowBot
{
    static private $flows; // Dynamic flows register from Flowdock
    static private $users; // Dynamic users register from Flowdock
    static private $plugins = array();
    static private $enabledFlows;
    static private $isInitialized = false;
    static private $isMultiFlows = false;

    /**
     * @description Initialize function to be called before using this class
     */
    static public function init()
    {
        echo "[INFO] Initializing PHPFlowBot..." . PHP_EOL;
        PHPFlowBot::printConfig();
        PHPFlowBot::loadUsers();
        PHPFlowBot::loadFlows();
        PHPFlowBot::loadPlugins();
        PHPFlowBot::registeringEnabledFlows();
        PHPFlowBot::$isInitialized = true;
    }

    /**
     * @param array $flow A flow definition
     * @param $message The message to send in the chat flow
     */
    static public function pushToFlow($flow, $message)
    {
        PHPFlow::pushToChat($flow->api_token, $message, BOT_NAME);
    }

    /**
     * Streaming flow(s)
     * @param null $flows One flow definition or an array or flows definition
     */
    static public function streamFlow($flows = null)
    {
        if (!PHPFlowBot::$isInitialized) {
            die ("[ERROR] PHPFlowBot not initialized. Please call PHPFlowBot::init() first.");
        }
        PHPFlowBot::validateFlowFormat($flows);

        // Message handler
        $callback = function ($ch, $jsonData) {
            return PHPFlowBot::callback($ch, $jsonData);
        };

        echo "[INFO] Start listening Flow(s)..." . PHP_EOL;
        if ((PHPFlowBot::$isMultiFlows = PFBFlowRegister::isMultipleFlowFormat($flows))) {
            $filter = array();
            foreach ($flows as $f) {
                $flowPath = $f['flow'][0] . FLOW_PATH_SEPARATOR_FILTER . $f['flow'][1];
                $filter[] = $flowPath;
                echo "[INFO]   - [" . $f['token'] . "] " . $flowPath . PHP_EOL;
            }
            PHPFlow::streamFlows(USER_TOKEN, $filter, $callback);
        } else {
            echo "[INFO]   - [" . $flows['token'] . "] " . $flows['flow'][0] . FLOW_PATH_SEPARATOR_FILTER . $flows['flow'][1] . PHP_EOL;
            PHPFlow::streamFlow(USER_TOKEN, $flows['flow'][0], $flows['flow'][1], $callback);
        }
    }

    /**
     * @description callback function that handle evey message received
     * @param $ch
     * @param $json_data
     * @return int
     */
    static public function callback($ch, $jsonData)
    {
        $length = strlen($jsonData);
        if (($length > 0) && ($jsonData[0] != FLOWDOCK_NEWLINE)) {
            $data = json_decode($jsonData);
            if (isset(PHPFlowBot::$flows[$data->flow])) {
                if (!IGNORE_BOT || (IGNORE_BOT && ($data->user != EXTERNAL_USER_ID))) {
                    $originFlow = PHPFlowBot::$flows[$data->flow];
                    if (VERBOSE_MODE) {
                        print_r($data);
                    } else if (PRINT_RECEIVING_EVENT) {
                        echo $jsonData;
                    }
                    PHPFlowBot::checkUser($data->user);
                    foreach (PHPFlowBot::$plugins as $plugin) {
                        $plugin->setOriginFlow($originFlow);
                        $plugin->run($data);
                    }
                }
            } else {
                echo "[WARNING] Can not identify the flow '" . $data->flow . "': event not handled." . PHP_EOL;
            }
        }
        return $length;
    }

    /**
     * @description Get user data from an id
     * @param $userId
     * @return mixed Return an array of user data if found, otherwise False.
     */
    static public function getUserById($userId)
    {
        if (isset(PHPFlowBot::$users[$userId])) {
            return PHPFlowBot::$users[$userId];
        }
        return false;
    }

    /**
     * @description Get user data from a nickname
     * @param $username
     * @return mixed Return an array of user data if found, otherwise False.
     */
    static public function getUserByUsername($username)
    {
        foreach (PHPFlowBot::$users as $user) {
            if ($user == $username) {
                return $user;
            }
        }
        return false;
    }

    /**
     * @description Check if user exist in memory, if not, trying to retrieve it
     * @param $userId
     */
    static private function checkUser($userId)
    {
        if (($userId != EXTERNAL_USER_ID) && (false === PHPFlowBot::getUserById($userId))) {
            echo "[INFO] New user(" . $userId . ") detected." . PHP_EOL;
            if (false !== ($result = PHPFlow::getUser(USER_TOKEN, $userId))) {
                $user = json_decode($result);
                PHPFlowBot::$users[$user->data] = $user;
            } else {
                echo "[WARNING] Couldn't retrieve user data." . PHP_EOL;
            }
        }
    }

    /**
     * @description Print configuration
     */
    static private function printConfig()
    {
        echo "[INFO] Configuration:" . PHP_EOL;
        echo "[INFO]   - USER_TOKEN            : " . USER_TOKEN . PHP_EOL;
        echo "[INFO]   - BOT_NAME              : " . BOT_NAME . PHP_EOL;
        echo "[INFO]   - IGNORE_BOT            : " . ((IGNORE_BOT) ? "Enabled" : "Disabled") . PHP_EOL;
        echo "[INFO]   - VERBOSE_MODE          : " . ((VERBOSE_MODE) ? "Enabled" : "Disabled") . PHP_EOL;
        echo "[INFO]   - PRINT_REVEIVING_EVENT : " . ((PRINT_RECEIVING_EVENT) ? "Enabled" : "Disabled") . PHP_EOL;
    }

    /**
     * @description Validate the $flow format or die with message
     * @param $flow
     */
    static private function validateFlowFormat($flow)
    {
        $errorFlowMessage = "[ERROR] The flow parameter should be like the format below:" . PHP_EOL
            . "array('flow' => array('COMPANY', 'FLOW_NAME'), 'token' => 'YOUR_FLOW_TOKEN')" . PHP_EOL
            . "or an array of this." . PHP_EOL . "Hope you can get through this, I know its not easy ;)" . PHP_EOL;
        PFBFlowRegister::checkFlowFormat($flow) or die($errorFlowMessage);
    }

    /**
     * @description Parse, Load and Format flows from PFBFlowRegister.class.php into $flows property
     * @return bool return True if success or die
     */
    static private function registeringEnabledFlows()
    {
        if (PFBFlowRegister::checkFlowFormat(PFBFlowRegister::$flows)) {
            echo "[INFO] Registering enabled flow(s)..." . PHP_EOL;
            foreach (PFBFlowRegister::$flows as $f) {
                $fname = $f["flow"][0] . FLOW_PATH_SEPARATOR . $f["flow"][1];
                PHPFlowBot::$enabledFlows[$fname] = $f;
                echo "[INFO]   - $fname enabled" . PHP_EOL;
            }
            echo "[INFO] " . count(PHPFlowBot::$enabledFlows) . " flow(s) enabled" . PHP_EOL;
            return true;
        } else {
            die("[ERROR] Registering enabled flows failed. Re-check your flows definitions syntax.");
        }
    }

    /**
     * @description Get and Load all users visible by the USER_TOKEN owner into $users property.
     * @return bool return True if success or die
     */
    static private function loadUsers()
    {
        echo "[INFO] Retrieving and loading users..." . PHP_EOL;
        ;
        if (false !== ($jsonUsers = PHPFlow::getUsers(USER_TOKEN))) {
            $users = json_decode($jsonUsers);
            foreach ($users as $user) {
                PHPFlowBot::$users[$user->id] = $user;
            }
            if (VERBOSE_MODE) {
                print_r(PHPFlowBot::$users);
            }
            echo "[INFO] " . count(PHPFlowBot::$users) . " user(s) loaded." . PHP_EOL;
            return true;
        }
        die("[ERROR] Retrieving users failed.");
    }

    /**
     * @description Load registered plugins
     * @return bool return True if success or die
     */
    static private function loadPlugins() {
        echo "[INFO] Loading plugins..." . PHP_EOL;
        $pluginsBaskter = new PFBPluginsBasket();
        if (false !== (PHPFlowBot::$plugins = $pluginsBaskter->loadPlugins())) {
            foreach (PHPFlowBot::$plugins as $plugin) {
                echo "[INFO]   - " . $plugin->getName() . " loaded" . PHP_EOL;
            }
            echo "[INFO] " . count(PHPFlowBot::$plugins) . " Plugins(s) loaded." . PHP_EOL;
            return true;
        } else {
            die("[ERROR] Plugins registered are malformated. It should be an array.");
        }
    }

    /**
     * @description Get and load all flows where USER_TOKEN or in or can joins
     * @return bool return True if success or die
     */
    static private function loadFlows(){
        echo "[INFO] Loading flow(s)..." . PHP_EOL;
        if (false !== ($flowsJson = PHPFlow::getAllFlows(USER_TOKEN))) {
            $flows = json_decode($flowsJson);
            foreach($flows as $f) {
                PHPFlowBot::$flows[$f->id] = $f;
                echo "[INFO]   - [".sprintf("%-'.36s", $f->id)."] " . $f->organization->name . "/" . $f->parameterized_name . PHP_EOL;
            }
            if (VERBOSE_MODE) {
                print_r(PHPFlowBot::$flows);
            }
            echo "[INFO] " . count(PHPFlowBot::$flows) . " flow(s) loaded." . PHP_EOL;
            return true;
        } else {
            die("[ERROR] Loading flows failed.");
        }
    }
}

?>