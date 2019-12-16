<?php
namespace handler;
use QYS\Protocol\Response;
use QYS\Protocol\Request;
use QYS\Task\Factory;
use QYS\Db\Mysql;
use QYS\Util\Debug;
use QYS\Log\Log;
use QYS\Db\Redis;
use globalunit\utils\DBHelper;
use globalunit\utils\config;
use globalunit\utils\JWTUtil;
use globalunit\utils\RequestHelper;
use globalunit\utils\Utils;
use globalunit\utils\KeysUtil;
use globalunit\utils\Common;
use QYS\Core\Config as QYSConfig;
use globalunit\utils\QueueHelper;
use Carbon\Carbon;


class LogHandler
{
    function __construct()
    {
    }

    // 用户下库
    public function on_user_userinfo($cmd) {
        $mysql = Mysql::getInstance("mysql1");
        $data=$cmd["data"];
        $id=$data["id"];
        $phone=$data["phone"];
        $nickname=$data["nickname"];
        $createtime=$data["createtime"];
        $usdt=$data["usdt"];
        $bsp=$data["bsp"];
        $through=$data["through"];
        $accountstatus=$data["accountstatus"];

        $sql = <<<crifan
        INSERT INTO
        s_member
            (id, phone, nickname, createtime,usdt,bsp,through,accountstatus)
        VALUES
            ("$id","$phone","$nickname","$createtime","$usdt","$bsp","$through","$accountstatus")
        ON DUPLICATE KEY UPDATE 
            usdt='{$usdt}',bsp='{$bsp}',through='{$through}',accountstatus='{$accountstatus}';
crifan;
        Log::var_dump($sql);
        $stmt = $mysql->prepare($sql);
        $stmt->execute();
        $stmt->close();
    }


    //提现记录下库
    public function on_wallet_withdrawrecord($cmd)
    {
        Log::var_dump($cmd);
        $mysql = Mysql::getInstance("mysql1");
        $data=$cmd["data"];
        $id=$data["orderid"].":".$data["uid"];
        $orderid=$data["orderid"];
        $uid=$data["uid"];
        $address=$data["address"];
        $symbol=$data["symbol"];
        $amount=$data["amount"];
        $status=$data["status"];
        $createtime=$data["createtime"];
        $sql = <<<crifan
        INSERT INTO
        s_withdrawrecord_log
            (id, uid, address,symbol,amount,createtime,status,orderid)
        VALUES
            ("$id","$uid","$address","$symbol","$amount","$createtime","$status","$orderid");
crifan;

        Log::var_dump($sql);
        $stmt = $mysql->prepare($sql);
        $stmt->execute();
        $stmt->close();
    }

    //充值记录下库
    public function on_wallet_rechargerecord($cmd)
    {
        Log::var_dump($cmd);
        $mysql = Mysql::getInstance("mysql1");
        $data=$cmd["data"];
        $id=$data["id"];
        $uid=$data["uid"];
        $address=$data["address"];
        $amount=$data["amount"];
        $symbol=$data["symbol"];
        $createtime=$data["time"];
        $sql = <<<crifan
        INSERT INTO
        s_rechargerecord_log
            (id, uid, address,amount,symbol,createtime)
        VALUES
            ("$id","$uid","$address","$amount","$symbol","$createtime");
crifan;

        Log::var_dump($sql);
        $stmt = $mysql->prepare($sql);
        $stmt->execute();
        $stmt->close();
    }


    //意见反馈记录下库
    public function on_hall_setfeedback($cmd)
    {
        $mysql = Mysql::getInstance("mysql1");
        $data=$cmd["data"];
        $uid=$data["uid"];
        $phone=$data["phone"];
        $content=$data["content"];
        $time=$data["time"];
        $sql = <<<crifan
        INSERT INTO
        s_feedback
            (uid, phone,content,time)
        VALUES
            ("$uid","$phone","$content","$time");
crifan;
        // Log::var_dump($sql);
        $stmt = $mysql->prepare($sql);
        $stmt->execute();
        $stmt->close();
    }

    //实名认证下库
    public function on_hall_authentication($cmd)
    {
        $mysql = Mysql::getInstance("mysql1");
        $data=$cmd["data"];
        $uid=$data["uid"];
        $name=$data["name"];
        $idcard=$data["idcard"];
        $time=$data["time"];
        $sql = <<<crifan
        INSERT INTO
        s_authentication
            (uid, name,idcard,time)
        VALUES
            ("$uid","$name","$idcard","$time");
crifan;
        // Log::var_dump($sql);
        $stmt = $mysql->prepare($sql);
        $stmt->execute();
        $stmt->close();
    }

    //购买时光机记录下库
    public function on_machine_machinelist($cmd){
        $mysql      = Mysql::getInstance("mysql1");

        $data       = $cmd["data"];
        $id         = $data["uid"].":".$data["id"];
        $uid        = $data["uid"];
        $price      = $data["price"];
        $pledge     = $data["pledge"];
        $type       = $data["type"];
        $status     = $data["status"];
        $machineid  = $data["id"];
        $createtime = $data["createtime"];

        $sql = <<<crifan
        INSERT INTO
        s_machinelist_log
            (`id`,`uid`, `price`,`pledge`, `type`,`status`, `createtime`,`machineid`)
        VALUES
            ("$id","$uid", "$price","$pledge", "$type","$status", "$createtime", "$machineid")
        ON DUPLICATE KEY UPDATE 
            price='{$price}',pledge='{$pledge}',type='{$type}',status='{$status}';
crifan;
        Log::var_dump($sql);

        $stmt = $mysql->prepare($sql);
        $stmt->execute();
        $stmt->close();
    }

    // 静态收益日志下库
    public function on_machine_machinelog($cmd) {
        $mysql      = Mysql::getInstance("mysql1");

        $data       = $cmd["data"];
        $uid        = $data["uid"];
        $type       = $data['type'];
        $bsp     = $data["bsp"];
        $through     = $data["through"];
        $createtime = $data["createtime"];
        $fromuid    = isset($data['fromuid'])?$data['fromuid']:0;
        $usdt    = isset($data['usdt'])?$data['usdt']:0;
        $machineid    = isset($data['machineid'])?$data['machineid']:0;

        $sql = <<<crifan
        INSERT INTO
        s_machinereward_log
            (`machineid`, `uid`, `bsp`,`through`, `type`, `createtime`, `usdt`,`fromuid`)
        VALUES
            ("$machineid", "$uid", "$bsp","$through", "$type", "$createtime", "$usdt", "$fromuid");
crifan;
        Log::var_dump($sql);
        $stmt = $mysql->prepare($sql);
        $stmt->execute();
        $stmt->close();
    }

    //res添加，减少记录
    public function on_res_change($cmd)
    {
        var_dump($cmd);
        $mysql = Mysql::getInstance("mysql1");
        $data=$cmd["data"];
        $uid=$data["uid"];
        $roleid=$data["roleid"]?:0;
        $num=$data["num"]?:0;
        $type=$data["type"]?:0;
        $t=$data["createtime"]?:0;
        $sql = <<<crifan
        INSERT INTO
        s_reschange_log
            (uid, roleid, num,type, createtime)
        VALUES
            ("$uid","$roleid","$num","$type","$t");
crifan;
        var_dump($sql);
        $mysql->query($sql);
    }

    public function handle($cmd)
    {
        $method = null;
        if(isset($cmd["c"]) && isset($cmd["m"])){
            $method="on_".$cmd["c"]."_".$cmd["m"];
        }

        if(method_exists($this,$method))
        {
            $this->$method($cmd);
        }
    }
}
