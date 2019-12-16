<?php
namespace model;
use globalunit\model\RedisPOB;
use QYS\Util\Debug;
use QYS\Log\Log;
use QYS\Core\Config as CoreConfig;
use globalunit\logic\UserLogic;
use QYS\Db\Redis;
use model\RedEnvelopeModel;

class WithdrawModel extends RedisPOB
{
    public $createtime = 0;
    public $amount     = 0;
    public $address    = "";
    public $symbol    = "";
    public $uid        = 0;
    public $orderid    = 0;
    public $status     = 0; //0 待审批   1 通过   2拒绝

    public function __construct($key,$r) {
        $this->createtime = time();
        parent::__construct($key,$r);
    }

    public function toparam($showprivate=false)
    {
        $a=parent::toparam();
        return $a;
    }

    public function save($except=array()){
        parent::save(array());
    }

    public function release(){
        $r=$this->_r;
        if(true){
            $r->expire($this->_key,0);
        }
    }
}

