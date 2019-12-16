<?php
/**
 * Created by PhpStorm.
 * User: chorkeung
 * Date: 2019/5/22
 * Time: 10:04 AM
 */
namespace logic;

use globalunit\utils\Config;
use globalunit\utils\KeysUtil;
use globalunit\utils\GenID;
use QYS\Util\Debug;
use QYS\Core\Config as CoreConfig;
use QYS\Log\Log;
use model\MachineModel;
use globalunit\logic\TuiJianLogic;
use globalunit\logic\RedisLogic;
use globalunit\logic\UserLogic;

class MachineLogic{

    public static function getMachineKey($machineid) {
        $key=KeysUtil::get_model_key("machines",$machineid);
        return $key;
    }

    public static function getListKey($uid) {
        $key=KeysUtil::get_zset_key('machinelist', $uid);
        return $key;
    }

    public static function loadMachine($rediscli, $machineid) {
        $key=self::getMachineKey($machineid);
        $u=new MachineModel($key,$rediscli);
        $u->load();
        return $u;
    }

    public static function createMachine($rediscli, $uid, $price) {
        $id = GenID::genid($rediscli,10000001,99999999,function($c){
            return self::getMachineKey($c);
        },"id");
        $m = self::loadMachine($rediscli, $id);
        $m->id      = $id;
        $m->uid     = $uid;
        $m->price   = $price;

        $m->save();

        $rediscli->zadd(self::getListKey($uid),time(), $id);

        return $m->toparam();
    }

    public static function delMachine($rediscli,$uid, $machineid) {
        $m = self::loadMachine($rediscli, $machineid);
        $m->release();
        $rediscli->zrem(self::getListKey($uid),$machineid);
    }

    public static function getmachinelist($rediscli,$index,$count,$uid){
        $all = [];
        $default = 100;
        $res=null;

        if (!empty($index) && !empty($count)) {
            $res = $rediscli->zrevrangebyscore(self::getListKey($uid),$index,$index-$count+1) or [];
        }else{
            $res = $rediscli->zrevrange(self::getListKey($uid),0,$default-1) or [];
        }

        $tem = [];
        foreach($res as $k=>$v){
            $itemmachine = self::loadMachine($rediscli, $v);
            array_push($tem, $itemmachine->toparam());
        }

        usort($tem,function($a,$b){
            return $b['createtime']- $a['createtime'];
        });

        return $tem;
    }

    //给上级增加USDT，超级舰长和顶级账号
    public static function releaseSuper($rediscli,$uid,$usdt,$machineid){
        $poweruid = $uid;//准备返利的uid,计算流水
        $aleader = TuiJianLogic::shangjilist($rediscli, $poweruid);

        if (empty($aleader)) {
            return 'ok';
        }

        $bget = false;  //舰长奖励已经被拿走
        $userpower = UserLogic::loaduser($rediscli, $poweruid);
        if($userpower->toparam()['userlvl']>0){
            return 'ok';
        }

        for ($i=0; $i < count($aleader); $i++) { 
            $uid    = $aleader[$i]['uid'];

            //等级计算
            $lv = UserLogic::getuserlvl($rediscli,$uid);
            
            if ($lv==0) {
                continue;
            }

            if ($bget==false && $lv==1){ //超级舰长获得每日2%加成
                $bget=true;
                $u = UserLogic::loaduser($rediscli, $uid);

                $realbili = config::get('captain', "machineadd");
                $totalth = $usdt*$realbili;
                $u->shangusdt($totalth);
                Log::var_dump("超级舰长".$uid.'获得伞下'.$poweruid.'usdt：'.$totalth);

                $u->load();
                QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());

                $log = [
                    'uid'           => $uid,
                    'bsp'           => 0,
                    'usdt'       => $totalth,
                    'type'          => 7,
                    'machineid'     => $machineid,
                    'fromuid'         => $poweruid,
                    'createtime'    => time(),
                ];
                QueueHelper::putLog(time().$uid, 'machine', 'machinelog', $log);
            }

            if ($lv==2){ //顶级账号每日2%加成
                $u = UserLogic::loaduser($rediscli, $uid);

                $realbili = config::get('captain', "superadd");
                $totalth = $usdt*$realbili;
                $u->shangusdt($totalth);

                $u->load();
                QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());
                Log::var_dump("顶级账号".$uid.'获得伞下'.$poweruid.'usdt：'.$totalth);

                $log = [
                    'uid'           => $uid,
                    'bsp'           => 0,
                    'usdt'       => $totalth,
                    'type'          => 8,
                    'machineid'     => $machineid,
                    'fromuid'         => $poweruid,
                    'createtime'    => time(),
                ];
                QueueHelper::putLog(time().$uid, 'machine', 'machinelog', $log);
            }
        }

        return 'ok';
    }
}
