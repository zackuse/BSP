<?php
namespace globalunit\logic;
use globalunit\model\UserModel;
use globalunit\utils\KeysUtil;
use QYS\Util\Debug;
use globalunit\utils\GenID;
use QYS\Log\Log;

Class LogLogic{
    public static function addlog($rediscli,$roomid,$index,$ctx)
    {
        $k=self::getkey($roomid);
        $rediscli->zadd($k,$index,json_encode($ctx));
    }

    public static  function getkey($roomid)
    {
        $mykey="loglogic";
        $key=KeysUtil::get_zset_key($mykey,$roomid);
        return $key;
    }

    public static function loadcontext($rediscli,$roomid)
    {
        $key=self::getkey($roomid);
        $res = $rediscli->zrange($key,-1,-1,True) or [];
        foreach($res as $k=>$v){
            $d=json_decode($k);
            return $d;
        }; 
    }

    public static function getmax($rediscli,$roomid)
    {
        $key=self::getkey($roomid);
        $res = $rediscli->zrange($key,-1,-1,True) or [];
        foreach($res as $k=>$v){
            $d=intval($v);
            return $d;
        }; 
    }

    public static function getlogs($rediscli,$roomid,$from,$to)
    {
        $key=self::getkey($roomid);
        $res = $rediscli->zrangebyscore($key,$from,$to,array("withscores"=>True)) or [];
        $tmp=array();
        foreach($res as $k=>$v){
            $d=json_decode($k);
            $tmp[]=$d;
        }; 
        return $tmp;
    }

    public static function getlog($rediscli,$roomid,$index)
    {
        $key=self::getkey($roomid);
        $res = $rediscli->zrangebyscore($key,$index,$index,array("withscores"=>True)) or [];
        $tmp=array();
        foreach($res as $k=>$v){
            $d=json_decode($k);
            $tmp[]=$d;
        }; 
        return $tmp;
    }
}


























