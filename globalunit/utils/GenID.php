<?php
namespace globalunit\utils;

class GenID
{
    public static $counter = 0;
    public static $prev = null;
    public static function gen()
    {
        return sha1(uniqid('_' . mt_rand(1, 1000000).getmypid(), true));
    }

    public static function genid($rediscli,$min,$max,$fmt,$mainkey)
    {
        $SCRIPT = <<<crifan
                local v = redis.call('exists', KEYS[1])
                if v==1 then
                   return nil
                end
                redis.call('hset',KEYS[1],KEYS[2],KEYS[3])
                return KEYS[3]
crifan;
        while(true){
            $r=mt_rand($min,$max);
            $k=$fmt($r);
            $args_args = Array($k,$mainkey,$r);
            $a=$rediscli->eval($SCRIPT,$args_args,3);
            if(isset($a)){
                return $r;
            }
        }
    }
}








