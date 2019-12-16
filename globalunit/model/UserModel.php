<?php
namespace globalunit\model;
use globalunit\model\RedisPOB;
use QYS\Util\Debug;
use QYS\Core\Config as CoreConfig;
use globalunit\logic\TuiJianLogic;
use globalunit\logic\UserLogic;
use globalunit\utils\Config;
use globalunit\utils\Utils;

Class UserModel extends RedisPOB
{
    public $id         = 0;
    public $createtime = 0;
    public $phone      = "";
    public $nickname   = '';
    public $shangji    = 0;
    public $avatar     = '';
    public $usdt       = 0;
    public $bsp        = 0;
    public $through    = 0;  //穿梭力
    public $daythrough = 0;  //当日穿梭力
    public $xxteakey   = ''; 
    public $accountstatus= 0;             //玩家状态 是否封号，暂留  0未封号, 1封号
    public $password     = '';
    public $jiaoyipassword     = '';
    public $caddr       = '';           //提现地址
    public $address = '';           //充值地址
    public $userlvl = 0;           //用户等级
    public $googlekey    = '';          //google登录密钥
    public $bindgoogle    = 0;          //是否绑定google
    public $isrealname    = 0;          //是否实名认证


    public function __construct($key,$r) {
        $this->createtime =  time();
        parent::__construct($key,$r);
    }

    public function save($except=array()){
        parent::save(array("usdt"=>1,"bsp"=>1,"through"=>1,"daythrough"=>1,));
    }

    public function toparam($showkey=false){
        $a=parent::toparam();
        if(!$showkey){
            unset($a['xxteakey']);
        }
        $fangda=CoreConfig::get("fangda");
        $a['usdt']=($a['usdt']/$fangda);
        $a['bsp']=($a['bsp']/$fangda);
        $a['through']=($a['through']/$fangda);
        $a['daythrough']=($a['daythrough']/$fangda);
        $a['avatar']='https://singervdshot.oss-cn-beijing.aliyuncs.com/bg_touxaing.png';
        $a['userlvl']=UserLogic::getuserlvl($this->_r,$this->id);

        return $a;
    }

    public function shangusdt($usdt)
    {
        $fangda = CoreConfig::get("fangda");
        $usdt    = $usdt*$fangda;
        $usdt    = floor($usdt);
        $r      = $this->_r;
        $k      = $this->_key;
        $r->hincrby($k,"usdt",$usdt);
    }

    public function xiausdt($usdt) {
        $fangda       = CoreConfig::get("fangda");
        $usdt          = $usdt*$fangda;
        $usdt          = floor($usdt);
        $r            = $this->_r;
        $k            = $this->_key;
        $SCRIPT       = <<<crifan
        local usdt     = KEYS[1]
        local k       = KEYS[2]
        local a       = redis.call('hget',k,'usdt') or 0
        a             = tonumber(a)
        usdt           = tonumber(usdt)
        if a>= usdt then
            local usdt = redis.call('hincrby',k,'usdt',-usdt)
            return "ok"
        else
            local usdt = redis.call('hget',k,'usdt')
            return "ng"
        end
crifan;
        $args_args = Array($usdt,$k);
        $a=$r->eval($SCRIPT,$args_args,2);
        return $a;
    }

    public function shangthrough($through)
    {
        $fangda = CoreConfig::get("fangda");
        $through    = $through*$fangda;
        $through    = floor($through);
        $r      = $this->_r;
        $k      = $this->_key;
        $r->hincrby($k,"through",$through);
    }

    public function xiathrough($through) {
        $fangda       = CoreConfig::get("fangda");
        $through          = $through*$fangda;
        $through          = floor($through);
        $r            = $this->_r;
        $k            = $this->_key;
        $SCRIPT       = <<<crifan
        local through     = KEYS[1]
        local k       = KEYS[2]
        local a       = redis.call('hget',k,'through') or 0
        a             = tonumber(a)
        through           = tonumber(through)
        if a>= through then
            local through = redis.call('hincrby',k,'through',-through)
            return "ok"
        else
            local through = redis.call('hget',k,'through')
            return "ng"
        end
crifan;
        $args_args = Array($through,$k);
        $a=$r->eval($SCRIPT,$args_args,2);
        return $a;
    }

    public function shangdaythrough($daythrough)
    {
        $fangda = CoreConfig::get("fangda");
        $daythrough    = $daythrough*$fangda;
        $daythrough    = floor($daythrough);
        $r      = $this->_r;
        $k      = $this->_key;
        $r->hincrby($k,"daythrough",$daythrough);
    }

    public function xiadaythrough($daythrough) {
        $fangda       = CoreConfig::get("fangda");
        $daythrough          = $daythrough*$fangda;
        $daythrough          = floor($daythrough);
        $r            = $this->_r;
        $k            = $this->_key;
        $SCRIPT       = <<<crifan
        local daythrough     = KEYS[1]
        local k       = KEYS[2]
        local a       = redis.call('hget',k,'daythrough') or 0
        a             = tonumber(a)
        daythrough           = tonumber(daythrough)
        if a>= daythrough then
            local daythrough = redis.call('hincrby',k,'daythrough',-daythrough)
            return "ok"
        else
            local daythrough = redis.call('hget',k,'daythrough')
            return "ng"
        end
crifan;
        $args_args = Array($daythrough,$k);
        $a=$r->eval($SCRIPT,$args_args,2);
        return $a;
    }

    public function shangbsp($bsp)
    {
        $fangda=CoreConfig::get("fangda");
        $bsp=floor($bsp*$fangda);
        $r=$this->_r;
        $k=$this->_key;
        $r->hincrby($k,"bsp",$bsp);
    }

    public function xiabsp($bsp)
    {
        $fangda=CoreConfig::get("fangda");
        $bsp=floor($bsp*$fangda);
        $r=$this->_r;
        $k=$this->_key;
        $SCRIPT = <<<crifan
        local bsp = KEYS[1]
        local k = KEYS[2]
        local a =redis.call('hget',k,'bsp') or 0
        a=tonumber(a)
        bsp=tonumber(bsp)
        if a>=bsp then
            local bsp = redis.call('hincrby',k,'bsp',-bsp)
            return "ok"
        else
            local bsp = redis.call('hget',k,'bsp')
            return "ng"
        end
crifan;

        $args_args = Array($bsp,$k);
        $a=$r->eval($SCRIPT,$args_args,2);
        return $a;
    }
}
