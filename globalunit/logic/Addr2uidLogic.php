<?php
namespace globalunit\logic;
use globalunit\utils\KeysUtil;
use QYS\Util\Debug;
use globalunit\utils\GenID;
use globalunit\utils\RndName;
use QYS\third\Crypto\XXTEA;
use QYS\Core\Config;
use Carbon\Carbon;
use QYS\Log\Log;

Class Addr2uidLogic{

    public static function getkey(){
        $key=KeysUtil::get_zset_key("add2uidlogic","map");
        return $key;
    }

    public static function add($rediscli,$uid,$addr){
        $rediscli->zadd(self::getkey(),$uid,strtolower($addr));
    }

    public static function addr2uid($rediscli,$addr){
        return $rediscli->zscore(self::getkey(),strtolower($addr));
    }

    public static function uid2addr($rediscli,$uid){
        $addr= $rediscli->zrevrangebyscore(self::getkey(),$uid,$uid);
        if(isset($addr)){
            return strtolower($addr);
        }
        return $addr;
    }
}
