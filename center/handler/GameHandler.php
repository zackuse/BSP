<?php
namespace handler;
use logic\AllShouyiLogic;
use QYS\Protocol\Response;
use QYS\Protocol\Request;
use QYS\Task\Factory;
use QYS\Db\Mysql;
use QYS\Util\Debug;
use QYS\Log\Log;
use QYS\Db\Redis;
use globalunit\model\UserModel;
use globalunit\utils\DBHelper;
use globalunit\utils\config;
use globalunit\utils\JWTUtil;
use globalunit\utils\RequestHelper;
use globalunit\utils\Utils;
use globalunit\utils\KeysUtil;
use QYS\Core\Config as QYSConfig;
use globalunit\logic\LogLogic;
use globalunit\utils\QueueHelper;
use globalunit\logic\UserLogic;
use globalunit\logic\TuiJianLogic;
use globalunit\logic\RedisLogic;
use globalunit\utils\MyLocker;
use QYS\Core\Config as CoreConfig;

class GameHandler
{
    function __construct()
    {
    }
    /**
     * 推荐奖励，静态收益
     * @param $cmd
     */
    public function on_test_test($cmd){

    }

    public function handle($cmd)
    {
        $method = null;
        if(isset($cmd["c"]) && isset($cmd["m"])){
            $method="on_".$cmd["c"]."_".$cmd["m"];
        }

        if(method_exists($this,$method))
        {
            $this->$method($cmd);
        }
    }
}
