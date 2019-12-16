<?php
/**
 * Created by PhpStorm.
 * User: chorkeung
 * Date: 2019/6/5
 * Time: 3:00 PM
 */

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

$uidsmap = array();
foreach ($result as $key => $value) {
    array_push($uidsmap, ["uid"=>$key,"createtime"=>$value]);
}
usort($uidsmap, function($a,$b){
    return $b["createtime"]-$a["createtime"];
});

foreach ($uidsmap as $k=>$value) {
    $poweruid = $value["uid"];//准备返利的uid,计算流水
    $aleader = TuiJianLogic::shangjilist($rediscli, $poweruid);

    if (empty($aleader)) {
        continue;
    }

    $bget = false; //舰长奖励已经被拿走
    $testuids = array();
    $through = 0;

    if (true) {
        $userpower = UserLogic::loaduser($rediscli, $poweruid);
        $through = $userpower->toparam()['daythrough'];
    }
    if($through==0){
        continue;
    }
    if($userpower->toparam()['userlvl']>0){
        continue;
    }

    for ($i=0; $i < count($aleader); $i++) { 
        $uid    = $aleader[$i]['uid'];
        array_push($testuids, $uid);

        //等级计算
        $lv = UserLogic::getuserlvl($rediscli,$uid);
        
        if ($lv==0) {
            continue;
        }

        if ($bget==false && $lv==1){ //超级舰长获得每日2%加成
            $bget=true;
            $u = UserLogic::loaduser($rediscli, $uid);

            $realbili = config::get('captain', "teamadd");
            $totalth = $through*$realbili;
            $u->shangthrough($totalth);
            Log::var_dump("超级舰长".$uid.'获得伞下'.$poweruid.'穿梭力：'.$totalth);

            $u->load();
            QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());

            $log = [
                'uid'           => $uid,
                'bsp'           => 0,
                'through'       => $totalth,
                'type'          => 5,
                'machineid'     => 0,
                'fromuid'         => $poweruid,
                'createtime'    => time(),
            ];
            QueueHelper::putLog(time().$uid, 'machine', 'machinelog', $log);
        }

        if ($lv==2){ //顶级账号每日2%加成
            $u = UserLogic::loaduser($rediscli, $uid);

            $realbili = config::get('captain', "teamadd");
            $totalth = $through*$realbili;
            $u->shangthrough($totalth);

            $u->load();
            QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());
            Log::var_dump("顶级账号".$uid.'获得伞下'.$poweruid.'穿梭力：'.$totalth);

            $log = [
                'uid'           => $uid,
                'bsp'           => 0,
                'through'       => $totalth,
                'type'          => 6,
                'machineid'     => 0,
                'fromuid'         => $poweruid,
                'createtime'    => time(),
            ];
            QueueHelper::putLog(time().$uid, 'machine', 'machinelog', $log);
        }
    }
    //每日穿梭力清零
    $userpower->xiadaythrough($through);
}