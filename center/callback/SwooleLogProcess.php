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

class SwooleLogProcess extends SwooleProcess
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
            $args_args = Array($LOG_QUEUE_BACKUP_NAME,$LOG_QUEUE_NAME);
            $a=$rediscli->eval($LPOPRPUSH,$args_args,2);
            while ($a!=null){
                $a=$rediscli->eval($LPOPRPUSH,$args_args,2);
            }
    }

    public function loop($worker, $work_id){
        $LOG_QUEUE_NAME="$this->gamename-logger-logqueue";
        $LOG_QUEUE_BACKUP_NAME="$this->gamename-logger-logqueuebackup";

        $params=array("worker"=>$worker->pid);
        $handler=CFactory::getInstance("handler\\LogHandler",$params);

//        Log::var_dump([$LOG_QUEUE_NAME,$LOG_QUEUE_BACKUP_NAME]);

        $r=Redis::newInstance("queue");
        $this->copyback($r);
        while (true) {
            try {
//                Log::var_dump([$LOG_QUEUE_NAME,$LOG_QUEUE_BACKUP_NAME]);
                $res = $r->brpoplpush($LOG_QUEUE_NAME,$LOG_QUEUE_BACKUP_NAME,10);

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
