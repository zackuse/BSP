<?php

namespace callback;

use QYS\Util\Formater;
use QYS\Protocol\Request;
use QYS\Protocol\Response;
use QYS\Socket\Callback\SwooleHttp as ZSwooleHttp;
use QYS\Socket\IClient;
use QYS\Core\Route as ZRoute;
use QYS\Log\Log;
use QYS\Util\Debug;
use QYS\Db\Redis;

use QYS\third\Pheanstalkd\Pheanstalk;
use QYS\third\Crypto\XXTEA;
use QYS\Core\Config;
use QYS\Core\Factory as CFactory;
use \Yosymfony\Toml\Toml;
use Swoole\Coroutine as co;
use globalunit\utils\Config as Cnf;
use globalunit\utils\QueueHelper;

use globalunit\utils\UniqueMessageQueueWithDelay;

// $array = Toml::Parse('key = [1,2,3]');

// print_r($array);

function strToHex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strToUpper($hex);
}

class SwooleHttp extends ZSwooleHttp
{
    protected  $serv;
    protected  $workerId;
    protected  $beanpid;
    protected  $taskpid;
    protected  $queuepid;

    public function onRequest($request, $response)
    {
        do{
            ZRoute::route($request,$response);
        }while(0);
    }

    public function copyback($rediscli){
        $appname=$GLOBALS['APPNAME'];
        $gamename=$GLOBALS['GAMENAME'];
        $LOG_QUEUE_NAME="$gamename-logger-logqueue";
        $LOG_QUEUE_BACKUP_NAME="$gamename-logger-logqueuebackup";

        $LPOPRPUSH = <<<crifan
                local v = redis.call('lpop', KEYS[1])
                if not v then
                    return nil
                end
                redis.call('rpush', KEYS[2], v)
                return v
crifan;
//        Debug::log(array("workerkid"=>$LPOPRPUSH));

        $args_args = Array($LOG_QUEUE_BACKUP_NAME,$LOG_QUEUE_NAME);
        $a=$rediscli->eval($LPOPRPUSH,$args_args,2);
        while ($a!=null){
            $a=$rediscli->eval($LPOPRPUSH,$args_args,2);
        }
    }


    public function runTaskProcess($server, $workerId){
        $r=Redis::getInstance("queue");
        $this->copyback($r);
        $gamename=$GLOBALS['GAMENAME'];
        $appname=$GLOBALS['APPNAME'];

        do{
            $process = new \swoole_process(function(\swoole_process $worker)use($workerId,$gamename,$appname){
                $LOG_QUEUE_NAME="$gamename-logger-logqueue";
                $LOG_QUEUE_BACKUP_NAME="$gamename-logger-logqueuebackup";

                $k1 = "$gamename-logger-logqueue";
                $params=array("workerId"=>$workerId);
                $handler=CFactory::getInstance("handler\\LogHandler",$params);

                $r=Redis::newInstance("queue");
                while (true) {
                    try {

                        $res = $r->brpoplpush($LOG_QUEUE_NAME,$LOG_QUEUE_BACKUP_NAME,30);

                        if($res)
                        {
                            $cmd=json_decode($res,true);
                            $handler->handle($cmd);
                            $r->lrem($LOG_QUEUE_BACKUP_NAME,$res,-1);
                        }
                    } catch (\RedisException $e) {
                        // var_dump($e);
                        // break;
                    }
                }
            }, false, false);
            $pid=$process->start();
            $this->taskpid = $pid;
        }while(false);
    }

    public function runQueueProcess($server, $workerId){
        do{
            $workercount=$server->setting['worker_num'];
            $appname=$GLOBALS['APPNAME'];
            $gamename=$GLOBALS['GAMENAME'];
            $process = new \swoole_process(function(\swoole_process $worker)use($gamename,$workercount,$workerId,$appname){
                $APPNAME=$GLOBALS['APPNAME'];
                $k1 = $appname."_queue_".$workerId;
                $k2 = $appname."_beanqueue_".$workerId;

                $params=array("workerId"=>$workerId);
                $handler=CFactory::getInstance("handler\\GameHandler",$params);
                $r=Redis::getInstance("queue");
                while (true) {
                    try {

                        $res = $r->brpop($k1,$k2,30);
                        if(isset($res[0])){
                            $k=$res[0];
                            $v=$res[1];
                            try {
                                $cmd=json_decode($v,true);
                                $handler->handle($cmd);
                            } catch (\Exception $e) {
                                // var_dump($e);
                            }
                        }
                    } catch (\RedisException $e) {
                        // var_dump($e);
                    }
                }
            });
            $pid=$process->start();
            $this->queuepid = $pid;
        }while(false);
    }

    public function runBeanProcess($server, $workerId){
        do{
            $workercount=$server->setting['worker_num'];
            $appname=$GLOBALS['APPNAME'];
            $gamename=$GLOBALS['GAMENAME'];
            $process = new \swoole_process(function(\swoole_process $worker)use($workerId,$gamename,$workercount,$appname){
                $r=Redis::getInstance("queue");
                $r1=Redis::getInstance("task");
                while (true) {
                    try {
                        $job=UniqueMessageQueueWithDelay::pop($r1,$appname);

                        if($job){
                            UniqueMessageQueueWithDelay::remove($r1,$appname,$job);
                            // Log::var_dump($job["body"]);
                            $roominfo = $job['body'];
                            $roomid=$roominfo['roomid'];
                            $index=$roomid%$workercount;
                            $queuename="$appname"."_beanqueue_"."$index";
                            $r->lpush($queuename,json_encode($job["body"]));
                        }else{
                           usleep(100*1000); 
                        }
                    } catch (\RedisException $e) {

                    } catch (\Exception $e) {
                        Log::exception($e);
                    }
                    
                }
            });
            $pid=$process->start();
            $this->beanpid = $pid;
        }while(false);
    }

    public function onWorkerStop($server, $workerId)
    {
        if(isset($this->taskpid)){
            \swoole_process::kill($this->taskpid);
        }

        if(isset($this->queuepid)){
            \swoole_process::kill($this->queuepid);
        }

        if(isset($this->beanpid)){
            \swoole_process::kill($this->beanpid);
        }
    }

    public function onWorkerStart($server, $workerId)
    {
        $GLOBALS['WORKERID'] = $workerId;
        $workercount=$server->setting['worker_num'];
        $GLOBALS['WORKERCOUNT'] = $workercount;

        $gamename=Config::get("gamename");
        $GLOBALS['GAMENAME'] = $gamename;

        $appname=Config::get("appname");
        $GLOBALS['APPNAME'] = $appname;

        $version=Config::get("version");
        $GLOBALS['VERSION'] = $version;

        Cnf::loadconfig();

        $this->serv = $server;
        $this->workerId = $workerId;

        if($server->taskworker){
            $has_log_handler = Config::get("LOG_HANDLER");
            if($has_log_handler && $workerId==($server->setting['worker_num'])){
                $this->runTaskProcess($server, $workerId);
            }
        }else{
            $has_game_handler = Config::get("GAME_HANDLER");
            if($has_game_handler){
                $this->runQueueProcess($server, $workerId);
                if($workerId==0){
                    $this->runBeanProcess($server,$workerId);
                }
            }
        }

        swoole_timer_tick(10*1000, function ($timer_id) use($server) {
            Cnf::loadconfig();
        });
        parent::onWorkerStart($server, $workerId);
    }
}
