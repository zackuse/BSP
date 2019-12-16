<?php
/**
 * Created by PhpStorm.
 * User: xhkj
 * Date: 2018/7/10
 * Time: 下午6:01
 */

namespace globalunit\utils;
use QYS\Db\Redis;
use QYS\Core\Config as QYSConfig;
use QYS\Cache\Factory as CFC;
use QYS\third\Pheanstalkd\Contract\PheanstalkInterface;
use globalunit\utils\UniqueMessageQueueWithDelay;

class QueueHelper
{
    public static function putBean($roomid,$c,$m,$data,$delay=0)
    {
        $tube=$GLOBALS['APPNAME'];
        // $bean=$GLOBALS['bean'];
        // $bean->useTube($tube);
        $r=Redis::getInstance("task");

        $cmd=array(
            "c"=>$c,
            "m"=>$m,
            "roomid"=>$roomid,
            "tp"=>'bean',
            "data"=>$data,
        );

        UniqueMessageQueueWithDelay::add($r,$tube,$cmd,$delay);
    }

    public static function putWorker($roomid,$c,$m,$data)
    {
        $r=Redis::getInstance("queue");
        $appname=$GLOBALS['APPNAME'];
        $workerId=$GLOBALS['WORKERID'];
        $workercount=$GLOBALS['WORKERCOUNT'];
        $index=$roomid%$workercount;
        $queuename=$appname."_queue_".$index;
        $cmd=array(
            "c"=>$c,
            "m"=>$m,
            "tp"=>'worker',
            "roomid"=>$roomid,
            "data"=>$data,
        );
        $r->lpush($queuename,json_encode($cmd));
    }

    public static function putLog($roomid,$c,$m,$data)
    {
        $r=Redis::getInstance("queue");
        $gamename=$GLOBALS['GAMENAME'];
        $queuename="$gamename-logger-logqueue";
        $cmd=array(
            "c"=>$c,
            "m"=>$m,
            "tp"=>'log',
            "roomid"=>$roomid,
            "data"=>$data,
        );
        $r->lpush($queuename,json_encode($cmd));
    }

    public static function putPub($roomid,$c,$m,$data)
    {
        $appname=$GLOBALS['APPNAME'];
        $r=Redis::getInstance("queue");
        $cmd=array(
            "c"=>$c,
            "m"=>$m,
            "roomid"=>$roomid,
            "tp"=>"msgcenter",
            "data"=>$data,
        );
        $pkg=array(
            "t"=>"room",
            "roomid"=>$appname."-".$roomid,
            "pkg"=>$cmd,
        );
        $r->lpush("msgcenter",json_encode($pkg));
    }
}

