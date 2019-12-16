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

class SwooleBeanProcess extends SwooleProcess
{   
    private $appname;
    private $gamename;
    private $version;
    private $lasttime;
    public function __construct()
    {
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

    public function loop($worker, $work_id){
        $r=Redis::getInstance("queue"); 
        $r1=Redis::getInstance("task");

        while (true) {
            try {
                $job=UniqueMessageQueueWithDelay::pop($r1,$this->appname);

                if($job){
                    UniqueMessageQueueWithDelay::remove($r1,$this->appname,$job);
                    $roominfo = $job['body'];
                    $roomid=$roominfo['roomid'];
                    $index=$roomid%1;
                    $queuename="$this->appname"."_beanqueue_"."$index";
                    $r->lpush($queuename,json_encode($job["body"]));
                }else{
                   if(empty($this->lasttime) || $this->lasttime<time()-30){
                        $this->lasttime=time();
                        $this->hearbeat();
                        Cnf::loadconfig();
                   }
                   usleep(600*1000); 
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    public function onWorkerStop($process){

    }
}
