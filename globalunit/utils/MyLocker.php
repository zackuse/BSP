<?php
/**
 * Created by PhpStorm.
 * User: xhkj
 * Date: 2018/8/10
 * Time: 下午1:54
 */
namespace globalunit\utils;
use QYS\Locker\RedLock;
use QYS\Core\Config as QYSConfig;
class MyLocker
{
    public  static $servers = null;

    private $lock_id = null;
    private $redLock = null;

    public function __construct($rediscli,$key,$sec,$count=3)
    {
        $redLock = new RedLock($rediscli);
        $this->redLock = $redLock;

        $lock_id = $redLock->lock($key, $sec);
        $this->lock_id=$lock_id;
    }

    public function islocked()
    {
        return $this->lock_id;
    }

    public  function  __destruct()
    {
        if($this->lock_id){
            $this->redLock->unlock($this->lock_id);
        }
    }
}


$config = QYSConfig::get('phpredis-lock');
MyLocker::$servers=[
    [$config['ip'],$config["port"],0.1],
];
