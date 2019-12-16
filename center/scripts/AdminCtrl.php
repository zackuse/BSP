<?php
namespace scripts;
use QYS\Protocol\Response;
use QYS\Protocol\Request;
use QYS\Db\Mysql;
use QYS\Db\Mongo;
use QYS\Log\Log;
use QYS\Db\Redis;
use globalunit\utils\DBHelper;
use globalunit\utils\config;
use globalunit\utils\JWTUtil;
use globalunit\utils\RndName;
use globalunit\utils\RequestHelper;
use globalunit\utils\Utils;
use globalunit\utils\KeysUtil;
use QYS\Core\Config as QYSConfig;
use globalunit\utils\MyLocker;
use globalunit\logic\UserLogic;
use logic\ShouYiLogic;
use globalunit\logic\TuiJianLogic;
use globalunit\logic\RedisLogic;
use QYS\third\Crypto\XXTEA;
use globalunit\utils\GenID;
use Carbon\Carbon;
use QYS\Core\Config as CoreConfig;
use globalunit\utils\QueueHelper;

class CMDADMIN
{
    //设置封号
    public static function setaccountstatus($request, $response, $rediscli){
        $uid = $request->getInt('uid');
        $accountstatus = $request->getInt('accountstatus');
        if (empty($uid) || !isset($accountstatus)) {
            return ['errcode'=>10201,];
        }

        $user = UserLogic::loaduser($rediscli, $uid);
        if ($user->id==0) {
            return ["errcode"=>10006,];
        }
        $user->accountstatus = $accountstatus;
        $user->save();
        $user->load();

        return ['errcode'=>0,'user'=>$user->toparam()];
    }

    //获取所有参数配置
    public static function getconfig($request, $response, $rediscli){
        $res = Config::getallconfig();

        unset($res['errmsg'] );
        unset($res['errmsgen']);

        return ['errcode'=>0,'data'=>$res];
    }

    //设置参数配置
    public static function setconfig($request, $response, $rediscli){
        $deploy = $request->getString('deploy');
        $data   = $request->getString('data');

        if (empty($deploy) || empty($data)) {
            return ['errcode'=>10201,];
        }

        $arr   = json_decode($data, true);

        $conn       = Mongo::getInstance('mongo1');
        $gamename   = $GLOBALS['GAMENAME'];
        $db         = $conn->$gamename;
        $collection = $db->gameconfig;

        foreach ($arr as $k=>$v) {
            if (empty($k) || empty($v)) {
                continue;
            }
            $collection->updateOne(["_id"=>$deploy,],['$set'=>[$k=>$v]],['upsert'=>true]);
            /*foreach ($v as $kk=>$vv) {
                $collection->updateOne(["_id"=>$k,],['$set'=>[$kk=>$vv]],['upsert'=>true]);
            }*/
        }

        return ['errcode'=>0,];
    }

    //res添加，减少
    public static function changeres($request, $response, $rediscli){
        $num = $request->getString('num');
        $uid = $request->getString('uid');
        $roleid = $request->getString('roleid');
        $type = $request->getString('type');
        if(!is_numeric($num) || $num==0 || !isset($type)){
            return ['errcode'=>10201,];
        }
        if($uid<1 || $roleid<1){
            return ['errcode'=>10201,];
        }

        if($type!="usdt" && $type!="bsp"){
            return ['errcode'=>10201,];
        }

        $u = UserLogic::loaduser($rediscli, $uid);
        
        if($u->id==0 || $uid!=$u->id){
            return ['errcode'=>10006,];
        }
        if($num>0){
            if ($type=='usdt') {
                $u->shangusdt($num);
            }
            if ($type=='bsp') {
                $u->shangbsp($num);
            }
        }else{
            if ($type=='usdt') {
                $u->xiausdt(-$num);
            }
            if ($type=='bsp') {
                $u->xiabsp(-$num);
            }
        }
        $u->save();
        $u->load();
        $data = [
            'uid'=>$uid,
            'roleid'=>$roleid,
            'num'=>$num,
            'type'=>$type,
            'createtime'=>time()
        ];
        QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());
        QueueHelper::putLog($u->id.time(), 'res', 'change', $data);

        return ['errcode'=>0,];
    }

    //修改会员等级
    public static function changeuserlvl($request, $response, $rediscli){
        $uid = $request->getString('uid');
        $level = $request->getInt('level');
        if($level<-1 || $level>2 || $uid<1){
            return ['errcode'=>10201,];
        }
        $u = UserLogic::loaduser($rediscli, $uid);
        
        if($u->id==0 || $uid!=$u->id){
            return ['errcode'=>10006,];
        }

        $u->userlvl = $level;
        $u->save();
        $u->load();

        return ['errcode'=>0,];
    }

    //获得玩家详情
    public static function userinfo($request, $response, $rediscli){
        $uid = $request->getString('uid');
        if($uid<1){
            return ['errcode'=>10201,];
        }
        $u = UserLogic::loaduser($rediscli, $uid);
        
        if($u->id==0 || $uid!=$u->id){
            return ['errcode'=>10006,];
        }
        return ['errcode'=>0,"data"=>["user"=>$u->toparam()]];
    }
}

class AdminCtrl
{
    public function test($request,$response,$params)
    {
        $result= array("errcode" => 10000, "data" =>["a"=>'1']);
        return $result;
    }

    public function invoke($request,$response,$params)
    {
        $response->addHeader("Content-Type", 'application/json');
        $response->addHeader("Access-Control-Allow-Origin", "*");
        $response->addHeader("Access-Control-Allow-Methods", 'POST, GET, OPTIONS, DELETE');
        $response->addHeader('Access-Control-Allow-Headers', "Origin, X-Requested-With, Content-Type, Accept");
        $response->sendHttpHeader();
        $fname = $params['fname'];
        $uid=$request->getString("uid");
        $rediscli = Redis::getInstance("redis1");

        $result=null;
        if(isset($uid)){
            $mainkey = KeysUtil::get_model_key("lock",$uid);
            $lockkey = "LOCK:".$mainkey;
            $lock = new MyLocker($rediscli,$lockkey,10);
            $result = null;
            if(!$lock->islocked()){
                $result= array("errcode" => 10000, "data" =>["a"=>'无法锁定']);
            }else{
                $result = CMDADMIN::$fname($request,$response,$rediscli);
            }
        }else{
            $result = CMDADMIN::$fname($request,$response,$rediscli);
        }

        if($result['errcode']!=0){
            $errcode = $result['errcode'];
            $result['errmsgen']=Config::get('errmsgen','e'.$errcode);
            $result['errmsg']=Config::get('errmsg','e'.$errcode);
        }

        Log::info('debug',$request,['fname'=>$fname,'result'=>$result]);
        $response->say(json_encode($result));
    }
}
