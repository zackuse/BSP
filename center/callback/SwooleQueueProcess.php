<?php

namespace callback;

use QYS\Util\Formater;
use QYS\Protocol\Request;
use QYS\Protocol\Response;
use QYS\Socket\Callback\SwooleProcess;
use QYS\Log\Log;
use QYS\Util\Debug;
use QYS\Db\Redis;
use QYS\Db\Mysql;
use QYS\Db\Mongo;
use QYS\third\Crypto\XXTEA;
use QYS\Core\Config;
use \Yosymfony\Toml\Toml;
use Swoole\Coroutine as co;
use globalunit\utils\Config as Cnf;
use globalunit\utils\QueueHelper;
use QYS\Core\Factory as CFactory;

use globalunit\utils\UniqueMessageQueueWithDelay;
function strToHex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strToUpper($hex);
}

class SwooleQueueProcess extends SwooleProcess
{   
    private $appname;
    private $gamename;
    private $version;
    private $lasttime;

    public function __construct()
    {
    }


    public function hearbeat()
    {
        $mysqlconfig = Config::get("mysql",array());
        foreach ($mysqlconfig as $key=>$value) {
            $conn = Mysql::getInstance($key);

            if($conn!=null){
                try{
                    $conn->ping();
                }catch (\Exception $e){

                }
            }
        }

        $redisconfig = Config::get("redis",array());
        foreach ($redisconfig as $key=>$value) {
            $instance = Redis::getInstance($key);
            try{
                $ret = $instance->get("a");
            }catch (\Exception $e){
            }
        }

        $mongoconfig = Config::get("mongo",array());
        foreach ($mongoconfig as $key=>$value) {
            $instance = Mongo::getInstance($key);
            try{
                $instance->listDatabases();
            }catch (\Exception $e){
            }
        }
    }

    public function onWorkerStart($process,$workerId,$work_num){
        $gamename=Config::get("gamename");
        $this->gamename=$gamename;

        $appname=Config::get("appname");
        $this->appname=$appname;
        

        $version=Config::get("version");
        $this->version=$version;

        $GLOBALS['GAMENAME'] = $gamename;
        $GLOBALS['APPNAME'] = $appname;
        $GLOBALS['VERSION'] = $version;

        $GLOBALS['WORKERID'] = $workerId;
        $GLOBALS['WORKERCOUNT'] = $work_num;

        Cnf::loadconfig();
    }

    public function loop($worker, $work_id){
        $k1 = $this->appname."_queue_".$worker->pid;
        $k2 = $this->appname."_beanqueue_".$work_id;

        $params=array("workerId"=>$worker->pid);
        $handler=CFactory::getInstance("handler\\GameHandler",$params);


        $r=Redis::getInstance("queue");
        Log::echo("i am queue".PHP_EOL);


        while (true) {
            try {
                $worker->write("heartbeat-".time());
                $res = $r->brpop($k1,$k2,30);
                if(isset($res[0])){
                    $k=$res[0];
                    $v=$res[1];
                    try {
                        $cmd=json_decode($v,true);
                        $handler->handle($cmd);
                    } catch (\Exception $e) {
                    }
                }
                $recv = $worker->pop();
                Log::var_dump([$recv]);
                if($recv && $recv=="quit"){
                    return false;
                }



            } catch (\RedisException $e) {
            }

            if(empty($this->lasttime) || $this->lasttime<time()-30){
                 $this->lasttime=time();
                 $this->hearbeat();
                 Cnf::loadconfig();
            }
        }
        return true;
    }

    public function onWorkerStop($process){

    }
}
