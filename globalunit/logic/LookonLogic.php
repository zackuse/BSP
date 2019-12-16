<?php
namespace globalunit\logic;
use globalunit\model\UserModel;
use globalunit\utils\KeysUtil;
use QYS\Util\Debug;
use globalunit\utils\GenID;



Class LookonLogic{
    public static  function getkey($roomid)
    {
        $mykey="lookon";
        $key=KeysUtil::get_zset_key($mykey,$roomid);
        return $key;
    }

    public static function add($rediscli,$roomid,$uid)
    {
        $k=self::getkey($roomid);
        $rediscli->zadd($k,time(),$uid);
    }

    public static function get($rediscli,$roomid)
    {
        $mykey=self::getkey($roomid);
        $res = $rediscli->zrange($mykey,0,-1,True) or [];
        $needremove=array();
        $uids=array();
        foreach($res as $k=>$v){
            $uid=intval($k);
            $time=intval($v);
            if(time()-$time > 120){
                $needremove[]=$uid;
            }else{
                $uids[]=$uid;
            }
        };
        foreach($needremove as $k=>$v){
            $rediscli->zrem($mykey,$v);
        };
        return ["uids"=>$uids,"needremove"=>$needremove];
    }
}


























