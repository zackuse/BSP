<?php

use QYS\Protocol\Response;
use QYS\Protocol\Request;
use QYS\Db\Mysql;
use QYS\Db\Redis;
use QYS\Log\Log;
use globalunit\utils\KeysUtil;
use globalunit\utils\QueueHelper;
use globalunit\utils\Config;
use globalunit\utils\Utils;
use globalunit\logic\TuiJianLogic;
use globalunit\logic\UserLogic;
use \Yosymfony\Toml\Toml;
use QYS\QYS;
use model\MachineModel;
use logic\MachineLogic;

// -----
$path    = joinPaths(QYS::getProjPath(),"..","config","prod.toml");
$myfile  = fopen($path, "r");
$content = fread($myfile,filesize($path));
$config  = Toml::Parse($content);

$gamename=$config['global']['gamename'];
$GLOBALS['GAMENAME'] = $gamename;

$fangda  =$config['global']['fangda'];
// $GLOBALS['FANGDA'] = $fangda;

// -----
Config::loadconfig();

$rediscli   = Redis::getInstance("redis1");
$mysql      = Mysql::getInstance("mysql1");

$modelkey = KeysUtil::get_zset_key('main', 'user');
$result = $rediscli->zrange($modelkey, 0, -1, True) or [];

foreach ($result as $uid => $createtime) {
    $keylist=MachineLogic::getListKey($uid);
    $res = $rediscli->zrange($keylist,0,-1,True) or [];
    foreach($res as $machineid=>$index){
        //静态释放奖励
        $item=MachineLogic::loadMachine($rediscli,$machineid);
        $price = $item->price;
        $pledge = $item->pledge;
        if ($pledge<=0) {
            continue;
        }
        //获得穿梭力
        $percentusdt = Config::get('machines_through',"usdt2through");
        $percentpledge = Config::get('machines_through',"pledge2through");
        $through = $price/$percentusdt*($pledge/$percentpledge);
        $u = UserLogic::loaduser($rediscli, $uid);
        $u->shangthrough($through);
        $u->shangdaythrough($through);

        //动态获得奖励1-质押获得穿梭力
        $map = Config::get('through');
        $pledge = min($pledge,500);
        $pledgeTh = $map[$pledge];
        $u->shangthrough($pledgeTh);
        $u->shangdaythrough($pledgeTh);
        $u->load();

        $bsp = 0;//释放的BSP
        $realpledge = $item->pledge;
        $totalth = $u->toparam()['through'];
        Log::var_dump('穿梭力:'.$totalth);
        //穿梭力超过$max后不能常规释放，需要配合质押的BSP释放。
        $max = config::get('captain', "max");
        if ($totalth<=$max && $realpledge<=500) {
            $bsp = $totalth*Config::get('machines_through',"price");
        }else{
            if ($totalth>$max && $realpledge<=500) {
                $bsp = $max*Config::get('machines_through',"price");
            }
            if ($totalth>$max && $realpledge>500) {
                $diff_th = $totalth-$max;
                $diff_pl = $realpledge-500;
                $exth = min($diff_th,$diff_pl*10);//额外可以释放多少个穿梭力
                $bsp = ($max+$exth)*Config::get('machines_through',"price");
            }
        }

        //如果超过1000万，执行第二方案,瓜分每日固定数量BSP
        $sqltotal = <<<crifan
        SELECT SUM(bsp) as totalin FROM s_member;
crifan;
        $restotal = $mysql->query($sqltotal);
        $ressx = $restotal->fetch_assoc();
        $sended = $ressx['totalin'] == '' ?0 :$ressx['totalin'];
        if ($sended>Config::get('BSP','plannum')) {
            $allbsp = Config::get('BSP','year1day');
            // 平台总穿梭力
            $sqltotal = <<<crifan
            SELECT SUM(through) as totalin FROM s_member;
crifan;
            $restotal = $mysql->query($sqltotal);
            $ressx = $restotal->fetch_assoc();
            $allth = $ressx['totalin'] == '' ?0 :$ressx['totalin'];
            $bsp = $allbsp/$allth*$totalth;
        }
        //第二方案end

        $u->shangbsp($bsp);

        $u->load();
        QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());

        $log = [
            'uid'           => $uid,
            'bsp'           => $bsp,
            'through'       => $through,
            'type'          => 1,
            'machineid'     => $item->id,
            'createtime'    => time(),
        ];
        QueueHelper::putLog(time().$uid, 'machine', 'machinelog', $log);

        $log2 = [
            'uid'           => $uid,
            'bsp'           => 0,
            'through'       => $pledgeTh,
            'type'          => 2,
            'machineid'     => $item->id,
            'createtime'    => time(),
        ];
        QueueHelper::putLog(time().$uid, 'machine', 'machinelog', $log2);
    }
}