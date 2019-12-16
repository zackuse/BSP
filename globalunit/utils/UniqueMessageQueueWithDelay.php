<?php
namespace globalunit\utils;
use QYS\Log\Log;
class UniqueMessageQueueWithDelay
{
    
    public static function add($red,$name,$data,$delay=0)
    {
        $bytes = random_bytes(5);
        $d=["jobid"=>bin2hex($bytes),"body"=>$data];
        $score = time() + $delay;
        $red->zadd($name,$score,json_encode($d));
    }


    public static function pop($red,$name)
    {
        $min_score = 0;
        $max_score = time();
        $res=$red->zRangeByScore($name, $min_score, $max_score, ['limit' => [0, 1]]);
        if(count($res)>0){
            return json_decode($res[0],true);
        }
        return null;
    }

    public static function remove($red,$name,$job)
    {
        $red->zrem($name,json_encode($job));
    }
}