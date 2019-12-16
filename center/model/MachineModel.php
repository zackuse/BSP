<?php
namespace model;
use globalunit\model\RedisPOB;
use QYS\Util\Debug;
use globalunit\utils\config;
use QYS\Core\Config as QYSConfig;
use QYS\Core\Config as CoreConfig;

//时光机
Class MachineModel extends RedisPOB{

    public $createtime  = 0;    // 创建时间
    public $id          = 0;    // 唯一id
    public $uid         = 0;    // 用户id
    public $price       = 0;    // 总金额USDT
    public $pledge      = 0;    // 质押bsp，大于0时光机释放才生效
    public $activate    = false;//时光机释放启动过

    public function __construct($key,$r) {
        $this->createtime = time();
        parent::__construct($key,$r);
    }

    public function save($except=array()){
        parent::save();
    }

    public function toparam(){
        $a=parent::toparam();
        //其他实时计算的数值
        $prices = Config::get('machines_price');
        for ($i=0; $i < count($prices); $i++) { 
            if ($prices[$i]==$this->price) {
                $a['type'] = $i+1;
            }
        }
        $a['type'] = $a['type']?:0;

        return $a;
    }

    public function release(){
        $r=$this->_r;
        if(true){
            $r->expire($this->_key,0);
        }
    }
}
