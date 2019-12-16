<?php
/**
 * Created by PhpStorm.
 * User: xhkj
 * Date: 2018/7/11
 * Time: 上午11:01
 */

namespace globalunit\utils;

class KeysUtil
{
public static function get_user_main_key($uid)
{
    $GAMENAME=  $GLOBALS['GAMENAME'];
    $key = $GAMENAME.":user:".$uid;
    return $key;
}

public static function get_main_usermap($uid)
{
    $GAMENAME=  $GLOBALS['GAMENAME'];
    $key = $GAMENAME.":usermap:".$uid;
    return $key;
}

public static function get_main_token($uid)
{
    $GAMENAME=  $GLOBALS['GAMENAME'];
    $key = $GAMENAME.":token:".$uid;
    return $key;
}

public static function get_model_key($key,$uid)
{
    $GAMENAME=  $GLOBALS['GAMENAME'];
    $key = $GAMENAME.":model:".$key.":".$uid;
    return $key;
}

public static function get_list_key($key,$uid)
{
    $GAMENAME=  $GLOBALS['GAMENAME'];
    $key = $GAMENAME.":list:".$key.":".$uid;
    return $key;
}

public static function get_list_key2($key,$subkey,$subsubkey)
{
    $GAMENAME=  $GLOBALS['GAMENAME'];
    $key = $GAMENAME.":list:".$key.":".$subkey.":".$subsubkey;
    return $key;
}

public static function get_zset_key($key,$subkey)
{
    $GAMENAME=  $GLOBALS['GAMENAME'];
    $key = $GAMENAME.":zset:".$key.":".$subkey;
    return $key;
}

public static function get_zset_key2($key,$subkey,$subsubkey)
{
    $GAMENAME=  $GLOBALS['GAMENAME'];
    $key = $GAMENAME.":zset:".$key.":".$subkey.":".$subsubkey;
    return $key;
}


}
