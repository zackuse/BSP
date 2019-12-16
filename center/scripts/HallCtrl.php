<?php
namespace scripts;
use QYS\Protocol\Response;
use QYS\Protocol\Request;
use QYS\Db\Mysql;
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
use globalunit\logic\Addr2uidLogic;
use logic\ShouYiLogic;
use globalunit\logic\TuiJianLogic;
use globalunit\logic\RedisLogic;
use globalunit\utils\Tools;
use QYS\third\Crypto\XXTEA;
use globalunit\utils\GenID;
use Carbon\Carbon;
use QYS\Core\Config as CoreConfig;
use globalunit\utils\QueueHelper;
use utils\SmsUtil;
use utils\OssUtil;
use logic\WalletLogic;
use logic\MachineLogic;
use utils\GoogleAuthenticator;
use logic\MarketLogic;

class CMDHALL
{
    public static function bindgoogle($request,$response,$rediscli){   
        $uid = $request->getInt("uid");
        $code = $request->getString("code");
        if(empty($uid) || empty($code)){
            return ["errcode"=>10006,];
        }
        $user=UserLogic::loaduser($rediscli,$uid);
        if ($user->id==0) {
            return ["errcode"=>10006,];
        }

        //TODO 验证Google
        $ga = new GoogleAuthenticator(); 
        $secret = $user->googlekey;
        $oneCode = $ga->getCode($secret); //服务端计算"一次性验证码",暂时用不上
        // 最后一个参数 为容差时间,这里是2 那么就是 2* 30 sec 一分钟.
         $checkResult = $ga->verifyCode($secret, $code, 2);
         if (!$checkResult) {
             return ["errcode"=>10123,];
         };
        $user->bindgoogle = 1;
        $user->save();
        $user->load();

        return ['errcode'=>0,"data"=>["user"=>$user->toparam(true,true),]];
    }

    //获得邀请码
    public static function getinvitecode($request,$response,$rediscli){
        $uid = $request->getInt("uid");

        if (empty($uid)) {
            return ["errcode"=>10006];
        }
        $u = UserLogic::loaduser($rediscli,$uid);

        $appurl = Config::get('tuiguang','appurl');
        $tuiguangurl = Config::get('tuiguang','tuiguangurl')."?itemId=$uid";

        return ['errcode'=>0,"data"=>["user"=>$u->toparam(),"appurl"=>$appurl,"tuiguangurl"=>$tuiguangurl]];
    }

    //获得公告
    public static function getnotice($request,$response,$rediscli){   
        $mysql = Mysql::getInstance("mysql1");
        $sql = <<<crifan
        SELECT * FROM s_notice ORDER BY createtime DESC;
crifan;
        $res = $mysql->query($sql);
        $res = $res->fetch_all(MYSQLI_ASSOC);

        return ['errcode'=>0,"data"=>["notice"=>$res]];
    }

    //获得我的团队
    public static function getmyteam($request,$response,$rediscli){
        $uid = $request->getInt("uid");
        if (empty($uid)) {
            return ["errcode"=>10006];
        }
        $u = UserLogic::loaduser($rediscli,$uid);
        if ($u->id==0) {
            return ["errcode"=>10006,];
        }

        $team = array();
        $mysql = Mysql::getInstance("mysql1");
        //我的直推节点
        $fanslvl1 = TuiJianLogic::xiajilistlevel($rediscli,$uid,1);
        for ($i=0; $i < count($fanslvl1); $i++) { 
            $uidlvl1 = $fanslvl1[$i]["uid"];
            $userlvl1 = UserLogic::loaduser($rediscli, $uidlvl1);
            $xiaudata = $userlvl1->toparam();

            //TODO分红从数据库取 s_machinereward_log 类型=2
            $sqltotal = <<<crifan
            SELECT SUM(through) as totalfh FROM s_machinereward_log WHERE `uid`=$uidlvl1;
crifan;
            $restotal = $mysql->query($sqltotal);
            $ressx = $restotal->fetch_assoc();
            $through = $ressx['totalfh'] == '' ?0 :$ressx['totalfh'];

            array_push($team, ["uid"=>$uidlvl1,"phone"=>$userlvl1->phone,"through"=>$through]);
        }

        return ['errcode'=>0,"data"=>["user"=>$u->toparam(),"team"=>$team,]];
    }

    //加载大厅
    public static function loadhall($request,$response,$rediscli){
        $mysql = Mysql::getInstance("mysql1");
        $uid = $request->getInt("uid");
        if (empty($uid)) {
            return ["errcode"=>10006];
        }
        $u = UserLogic::loaduser($rediscli,$uid);
        if ($u->id==0) {
            return ["errcode"=>10006,];
        }

        $machinelist = MachineLogic::getmachinelist($rediscli,false,false,$uid);
        //TODO 数据内容需要确认
        $bord = array();
        $bord['plan'] = Config::get('BSP','plan');
        // 已发放，当前所有玩家身上bsp汇总，mysql成员列表里查。
        $sqltotal = <<<crifan
        SELECT SUM(bsp) as totalin FROM s_member;
crifan;
        $restotal = $mysql->query($sqltotal);
        $ressx = $restotal->fetch_assoc();
        $bord['sended'] = $ressx['totalin'] == '' ?0 :$ressx['totalin'];
        // 昨日BSP奖励 ，昨天所有玩家获得的BSP，mysql- s_machinereward_log
        $yesterday = Carbon::yesterday("Asia/Shanghai")->timestamp;
        $todaybegin = Carbon::today("Asia/Shanghai")->timestamp;
        $sqltotal = <<<crifan
        SELECT SUM(bsp) as yesterday FROM s_machinereward_log WHERE `createtime`>=$yesterday and `createtime`<$todaybegin;
crifan;
        $restotal = $mysql->query($sqltotal);
        $ressx = $restotal->fetch_assoc();
        $bord['yesterday'] = $ressx['yesterday'] == '' ?0 :$ressx['yesterday'];
        // 平台总穿梭力
        $sqltotal = <<<crifan
        SELECT SUM(through) as totalin FROM s_member;
crifan;
        $restotal = $mysql->query($sqltotal);
        $ressx = $restotal->fetch_assoc();
        $bord['total'] = $ressx['totalin'] == '' ?0 :$ressx['totalin'];
        $bord['personal'] = $u->toparam()["through"];
        if ($bord['yesterday']>0) {
            $bord['reward'] = round((Config::get('BSP','year1day')/$bord['yesterday']),4);
        }else{
            $bord['reward'] = round((Config::get('BSP','year1day')),4);
        }

        return ['errcode'=>0,"data"=>["user"=>$u->toparam(),"machinelist"=>$machinelist,"bord"=>$bord]];
    }

    //获得我的资产
    public static function getmyassets($request,$response,$rediscli){
        $uid = $request->getInt("uid");
        if (empty($uid)) {
            return ["errcode"=>10006];
        }
        $u = UserLogic::loaduser($rediscli,$uid);

        //总资产
        $price = Config::get('BSP','price');
        $usdttotal = $u->toparam()['usdt']+$u->toparam()['bsp']*$price;

        $ratelist = MarketLogic::loadrate($rediscli);
        $usdt2cny = 0;
        foreach ($ratelist as $key => $value) {
            if($value["symbol"]=='USDT'){
                $ratelist[$key]['count'] = $u->toparam()['usdt'];
                $usdt2cny = $value["cnyprice"];
            }
        }

        $cnytotal = $usdttotal*$usdt2cny;

        $listcurrency = array();
        array_push($listcurrency, ['type'=>"BSP",'count'=>$u->toparam()["bsp"],'cny'=>$u->toparam()['bsp']*$price*$usdt2cny]);
        array_push($listcurrency, ['type'=>"USDT",'count'=>$u->toparam()["usdt"],'cny'=>$u->toparam()['usdt']*$usdt2cny]);

        return ['errcode'=>0,"data"=>["user"=>$u->toparam(),"usdttotal"=>$usdttotal,"cnytotal"=>$cnytotal,'listcurrency'=>$listcurrency]];
    }

    //设置反馈
    public static function setfeedback($request,$response,$rediscli){  
        $content = $request->getString("content");

        if(!isset($content)){
            return ["errcode"=>10203,];
        }

        if(strlen($content)>=255){
            return ["errcode"=>10114,];
        }

        $uid = $request->getInt("uid");
        if (empty($uid)) {
            return ["errcode"=>10006];
        }
        $user=UserLogic::loaduser($rediscli,$uid);
        if ($user->id==0) {
            return ["errcode"=>10006,];
        }

        $data = array(
            "uid"=>$uid,
            "phone"=>$user->phone,
            "content"=>$content,
            "time"=>time(),
        );

        QueueHelper::putLog($uid.time(),"hall","setfeedback",$data);

        return ['errcode'=>0,"data"=>[]];
    }

    //设置实名认证
    public static function authentication($request,$response,$rediscli){  
        $name = $request->getString("name");
        $idcard = $request->getString("idcard");

        if(!isset($name) || !isset($idcard)){
            return ["errcode"=>10001,];
        }

        $uid = $request->getInt("uid");
        if (empty($uid)) {
            return ["errcode"=>10006];
        }

        $ok = Tools::idCard($idcard,$name);
        if ($ok==false) {
            return ["errcode"=>10132];
        }

        $user=UserLogic::loaduser($rediscli,$uid);
        if ($user->id==0) {
            return ["errcode"=>10006,];
        }

        $user->isrealname = 1;
        $user->save();
        $user->load();

        $data = array(
            "uid"=>$uid,
            "name"=>$name,
            "idcard"=>$idcard,
            "time"=>time(),
        );

        QueueHelper::putLog($uid.time(),"hall","authentication",$data);

        return ['errcode'=>0,"data"=>[]];
    }
}


class HallCtrl
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
                $result = CMDHALL::$fname($request,$response,$rediscli);
            }
        }else{
            $result = CMDHALL::$fname($request,$response,$rediscli);
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
