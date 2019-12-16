<?php
namespace globalunit\logic;
use globalunit\utils\KeysUtil;
use globalunit\utils\GenID;


Class RedisLogic{

    //get
    //通过phone获取uid
    public static function get_uid_byphone($rediscli,$phone)
    {
        $mapkey = KeysUtil::get_main_usermap($phone);
        $uid = $rediscli->get($mapkey);
        return $uid;
    }

    //通过助记词获取uid
    public static function get_uid_bymnword($rediscli,$mnword)
    {
        $mapkey = KeysUtil::get_main_usermap($mnword);
        $uid = $rediscli->get($mapkey);
        return $uid;
    }

    //设置token
    public static function set_tokenkey($rediscli,$uid)
    {
        $token = GenID::gen();
        $tokenkey = KeysUtil::get_main_token($uid);
        $rediscli->set($tokenkey,$token);
        return $token;
    }

    //添加玩家到所有玩家列表
    public static function adduser_toalllist($rediscli,$uid)
    {
        $key = KeysUtil::get_zset_key("main","user");
        $rediscli->zadd($key,time(),$uid);
    }
}
