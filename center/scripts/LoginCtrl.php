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
use QYS\third\Crypto\XXTEA;
use globalunit\utils\GenID;
use Carbon\Carbon;
use QYS\Core\Config as CoreConfig;
use globalunit\utils\QueueHelper;
use utils\SmsUtil;
use utils\OssUtil;
use logic\WalletLogic;
use utils\GoogleAuthenticator;

class CMDLOGIN
{
    public static function test($request,$response,$rediscli)
    {
        return array("errcode"=>0,"data"=>["a"=>1,]);
    }

    public static function register($request,$response,$rediscli){
        $phone = $request->getString("phone");
        $password = $request->getString("password");
        $code = $request->getString("code");
        $nickname = $request->getString("nickname");
        $yaoqingma = $request->getString("yaoqingma");
        $jiaoyipassword = $request->getString("jiaoyipassword");

        if(!isset($phone) || !isset($password)|| !isset($code) || !isset($jiaoyipassword) || !isset($nickname)){
            return ["errcode"=>10001,];
        }

        //通过phone获取uid
        $uid = RedisLogic::get_uid_byphone($rediscli,$phone);
        if(!empty($uid)){
            return ["errcode"=>10003,];
        }
        //邀请码无效
        $agency=UserLogic::loaduser($rediscli,$yaoqingma);
        if (isset($yaoqingma) && $agency->id==0 && $yaoqingma!=123456789) {
            return ["errcode"=>10113,];
        }

        //验证码
        $key = $GLOBALS['GAMENAME'] . ":code:" . $phone;
        $checkcode = $rediscli->get($key);
        if (empty($checkcode)) {
            // return ["errcode"=>10002,];
        }

        if ($checkcode != $code) {
            // return ["errcode"=>10002,];
        }

        //创建usdt钱包
        $address = "address";
        // $result = WalletLogic::getAddress();

        // if(empty($result)){
        //     return ["errcode"=>10401,];
        // }
        // if(isset($result['errcode']) && $result['errcode'] != 0){
        //     return ["errcode"=>10401,];
        // }
        // $address = $result["data"]["address"];

        $data=array(
            "phone"=>$phone,
            "password"=>$password,
            "jiaoyipwd"=>$jiaoyipassword,
            "address"=>$address,
            "nickname"=>$nickname,
        );

        $user = UserLogic::register($rediscli,$data);
        if($user['id']==0){
            return ["errcode"=>10004,];
        }

        //usdt绑定uid
        Addr2uidLogic::add($rediscli,$user['id'],$address);
        
        //设置token
        $token = RedisLogic::set_tokenkey($rediscli,$user['id']);

        $u=UserLogic::loaduser($rediscli,$user['id']);
        //TODO 上线删除测试代码
        $u->shangusdt(1000000);
        $u->shangbsp(1000000);

        $GAMENAME=  $GLOBALS['GAMENAME'];
        $VERSION=  $GLOBALS['VERSION'];
        $jwt = JWTUtil::encode(array("uid" => $user["id"], "token" =>$token), "$GAMENAME-$VERSION", 'HS256');

        // 添加玩家列表
        RedisLogic::adduser_toalllist($rediscli,$user['id']);

        //设置Googlekey
        $ga = new GoogleAuthenticator(); //创建一个新的"安全密匙SecretKey" //把本次的"安全密匙SecretKey" 入库,和账户关系绑定,客户端也是绑定这同一个"安全密匙SecretKey"
        $secret = $ga->createSecret();
        $u->googlekey = $secret;

        //绑定上级操作
        $yaoqingma = $yaoqingma?:123456789;
        TuiJianLogic::add($rediscli,$u->id,$yaoqingma,time());
        $u->shangji = $yaoqingma;
        $u->save();
        $u->load();

        QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());

        return ['errcode'=>0,"data"=>["user"=>$u->toparam(),"jwt"=>$jwt]];
    }

    public static function login($request,$response,$rediscli){
        $phone = $request->getString("phone");
        $password = $request->getString("password");
        $qrcode = $request->getString("qrcode");
        if(!isset($phone)||!isset($password)||!isset($qrcode)){
            return ["errcode"=>10001,];
        }

        //通过phone获取uid
        $uid = RedisLogic::get_uid_byphone($rediscli,$phone);
        if(empty($uid)){
            return ["errcode"=>10005,];
        }

        //图形验证
        $key = $GLOBALS['GAMENAME'] . ":qrcode:" . $phone;
        $code = $rediscli->get($key);
        if ($qrcode!=$code) {
            return ["errcode"=>10127,];
        }

        $user=UserLogic::loaduser($rediscli,$uid);
        if($user->id==0){
            return ["errcode"=>10006,];
        }
        
        if($user->accountstatus == 1){
            return ["errcode"=>10008,];
        }

        if($user->password!=$password){
            return ["errcode"=>10007,];
        }
        $user->load();
        QueueHelper::putLog($user->id.time(), 'user', 'userinfo', $user->toparam());

        //设置token
        $token = RedisLogic::set_tokenkey($rediscli,$user->id);
        $GAMENAME=  $GLOBALS['GAMENAME'];
        $VERSION=  $GLOBALS['VERSION'];
        $jwt = JWTUtil::encode(array("uid" => $user->id, "token" =>$token), "$GAMENAME-$VERSION", 'HS256');

        return ['errcode'=>0,"data"=>["user"=>$user->toparam(true),"jwt"=>$jwt]];
    }

    public static function loaduser($request,$response,$rediscli){   
        $uid = $request->getInt("uid");
        if(empty($uid)){
            return ["errcode"=>10006,];
        }
        $user=UserLogic::loaduser($rediscli,$uid);
        if ($user->id==0) {
            return ["errcode"=>10006,];
        }
        $data = $user->toparam(true);

        return ['errcode'=>0,"data"=>["user"=>$data]];
    }

    //找回密码
    public static function findpassword($request,$response,$rediscli){
        $phone = $request->getString("phone");
        $password = $request->getString("password");
        $code = $request->getString("code");
        $codegoogle = $request->getString("codegoogle");

        if(!isset($phone) || !isset($password) || !isset($code) || !isset($codegoogle)){
            return ["errcode"=>10102,];
        }

        $uid = RedisLogic::get_uid_byphone($rediscli,$phone);
        if (empty($uid)) {
            return ["errcode"=>10006];
        }
        $u = UserLogic::loaduser($rediscli,$uid);

        //TODO 验证Google
        $ga = new GoogleAuthenticator(); 
        $secret = $u->googlekey;
        $oneCode = $ga->getCode($secret); //服务端计算"一次性验证码",暂时用不上
        // 最后一个参数 为容差时间,这里是2 那么就是 2* 30 sec 一分钟.
         $checkResult = $ga->verifyCode($secret, $codegoogle, 2);
         if (!$checkResult) {
             return ["errcode"=>10123,];
         };

        //校验验证码
        $key = $GLOBALS['GAMENAME'] . ":code:" . $phone;
        $checkcode = $rediscli->get($key);
        if (empty($checkcode)) {
            // return ["errcode"=>10002,];
        }

        if ($checkcode != $code) {
            // return ["errcode"=>10002,];
        }

        if($phone!=$u->phone){
            return ["errcode"=>10203,];
        }

        $u->password = $password;
        $u->save();
        $u->load();

        return ['errcode'=>0,"data"=>["user"=>$u->toparam(),]];
    }

    //创锐
    public static function sms_cr($request, $response, $rediscli) {
        $phone  = $request->getInt('phone');
        $nihaoma  = $request->getString('nihaoma');
        if (empty($phone)) {
            return ['errcode'=>10009,];
        }

        //校验是不是客户端传来的
        $keycheck = "nihaoma";
        $strcheck = md5($phone.$keycheck);
        if ($nihaoma!=$strcheck) {
            // return ['errcode'=>10207,];
        }
        
        // 存code
        $key = $GLOBALS['GAMENAME'] . ":code:" . $phone;
        $res = SmsUtil::smssend_cr($phone);
        if (isset($res["code"])) {
            $rediscli->set($key,$res["code"]);
            $rediscli->expire($key,300);
        }

        return ['errcode'=>0, 'data'=>[],];
    }

    //创锐国际
    public static function sms_cr_inter($request, $response, $rediscli) {
        $phone  = $request->getInt('phone');
        $nihaoma  = $request->getString('nihaoma');
        if (empty($phone)) {
            return ['errcode'=>10009,];
        }

        //校验是不是客户端传来的
        $keycheck = "nihaoma";
        $strcheck = md5($phone.$keycheck);
        if ($nihaoma!=$strcheck) {
            // return ['errcode'=>10207,];
        }

        if(preg_match("/^1[3456789]{1}\d{9}$/",$phone)){
            $res = SmsUtil::smssend_cr_inter("86".$phone);//中国
        }else{
            $res = SmsUtil::smssend_cr_inter($phone);//国际
        }
        
        $key = $GLOBALS['GAMENAME'] . ":code:" . $phone;
        if (isset($res["code"])) {
            $rediscli->set($key,$res["code"]);
            $rediscli->expire($key,300);
        }

        return ['errcode'=>0, 'data'=>[],];
    }

    //图形验证
    public static function getqrcode($request, $response, $rediscli) {
        $phone  = $request->getInt('phone');
        if(!isset($phone)){
            return ["errcode"=>10001,];
        }

        // 存code
        $key = $GLOBALS['GAMENAME'] . ":qrcode:" . $phone;
        $code = rand(1000, 9999);
        $rediscli->set($key,$code);

        return ['errcode'=>0, 'data'=>["qrcode"=>$code],];
    }
}


class LoginCtrl
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
                $result = CMDLOGIN::$fname($request,$response,$rediscli);
            }
        }else{
            $result = CMDLOGIN::$fname($request,$response,$rediscli);
        }

        if($result['errcode']!=0){
            $errcode = $result['errcode'];
            $result['errmsg']=Config::get('errmsg','e'.$errcode);
            $result['errmsgen']=Config::get('errmsgen','e'.$errcode);
        }

        Log::info('debug',$request,['fname'=>$fname,'result'=>$result]);
        $response->say(json_encode($result));
    }
}
