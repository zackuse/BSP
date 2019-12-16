<?php
/**
 * Created by PhpStorm.
 * User: chorkeung
 * Date: 2019/5/16
 * Time: 10:30 AM
 */
namespace scripts;

use QYS\Protocol\Response;
use QYS\Protocol\Request;
use QYS\Db\Mysql;
use QYS\Db\Redis;
use QYS\Log\Log;
use globalunit\utils\config;
use globalunit\utils\KeysUtil;
use globalunit\utils\MyLocker;
use globalunit\logic\UserLogic;
use globalunit\utils\GenID;
use globalunit\logic\Addr2uidLogic;
use QYS\Core\Config as CoreConfig;
use globalunit\utils\QueueHelper;
use model\WithdrawModel;
use logic\WalletLogic;
use utils\GoogleAuthenticator;

class CMDWALLET {

    public static function checkparam($params, $signame) {
        $cloneparam = $params;
        unset($cloneparam[$signame]);
        $keys=array();
        foreach ($cloneparam as $key => $value) {
            array_push($keys, $key);
        }
        sort($keys);

        $tmp = array();
        for ($i=0; $i < count($keys); $i++) { 
            $v = $cloneparam[$keys[$i]];
            array_push($tmp, "$keys[$i]=$v");
        }

        $encodeparams = join("&",$tmp);
        $sign = strtoupper(md5($encodeparams));
        return $sign == strtoupper($params[$signame]);
    }
    
    /**
     * 充值回调 key
     * @param $curr
     * @return string
     */
    public static function calldeposit($request, $response, $rediscli) {
        $params = $request->getRequest()->post;
        $data = $params['data'];
        $hash = $data['hash'];
        $address_to = $data['address_to'];
        $amount = $data['amount'];
        $symbol = $data['symbol'];
        $merchantid = $params['merchantid'];
        $sign = $params['sign'];
        $signType = $params['signType'];

        if (empty($signType) || strtoupper($signType)!='MD5') {
            return ['errcode' => 10201,];
        }

        if (empty($address_to) || empty($symbol) || empty($merchantid)) {
            return ['errcode' => 10201,];
        }

        if (strtoupper($symbol)!='USDT' && strtoupper($symbol)!='BSP') {
            return ['errcode' => 10201,];
        }

        if (self::checkparam($params,"sign")==false) {
            return ['errcode' => 10201,];
        }

        //开始操作程序内部上分
        $uid = Addr2uidLogic::addr2uid($rediscli,$address_to);
        Log::var_dump("充值uid:".$uid);

        //多次处理return
        $mainkey = KeysUtil::get_zset_key('deposit2hash', "hash");
        $res = $rediscli->zrangebyscore($mainkey,$uid,$uid,array("withscores"=>True));
        if ($res) {
            foreach($res as $k=>$v){
                if ($k==$hash) {
                    Log::var_dump("hash 不过:".$hash);
                    return 'SUCCESS';
                }
            }; 

            $rediscli->zadd($mainkey,$uid,$hash);

            $res = $rediscli->zrangebyscore($mainkey,$uid,$uid,array("withscores"=>True));
            foreach($res as $k=>$v){
                if ($v!=$hash) {
                    $rediscli->zrem($mainkey,$uid,$v);
                }
            }; 
        }else{
            $rediscli->zadd($mainkey,$uid,$hash);
        }

        if (isset($uid)) {
            $mainkey = KeysUtil::get_user_main_key($uid);
            $lockkey = "LOCK:".$mainkey;
            $lock = new MyLocker($rediscli,$lockkey,10);
            if($lock->islocked()){
                $u = UserLogic::loaduser($rediscli,$uid);
                if (strtoupper($symbol) == 'USDT') {
                    $u->shangusdt($amount);
                }else if(strtoupper($symbol) == 'BSP'){
                    $u->shangbsp($amount);
                }
                Log::var_dump("充值$symbol:".$amount);

                $u->load();
                QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());
                // TODO:插入充值记录，mysql
                $data = array(
                    "id"=>$uid.":".time().":".$hash,
                    "uid"=>$uid,
                    "amount"=>$amount,
                    "symbol"=>$symbol,
                    "address"=>$address_to,
                    "time"=>time(),
                );
                $recharge = CoreConfig::get("recharge");
                QueueHelper::putLog($recharge,"wallet","rechargerecord",$data);
            }
        }

        return 'SUCCESS';
    }

    /**
     * 提款回调 key
     * @param $curr
     * @return string
     */
    public static function callwithdrawal($request, $response, $rediscli) {
        $params = $request->getRequest()->post;
        $symbol = $params['symbol'];
        $amount = $params['amount'];
        $merchantid = $params['merchantid'];
        $sign = $params['sign'];
        $signType = $params['signType'];
        $address_to = $params['address_to'];

        if (empty($signType) || strtoupper($signType)!='MD5') {
            return ['errcode' => 10201,];
        }

        if (empty($address_to) || empty($symbol) || empty($merchantid)) {
            return ['errcode' => 10201,];
        }

        if (strtoupper($symbol)!='USDT' && strtoupper($symbol)!='BSP') {
            return ['errcode' => 10201,];
        }

        $amount = intval($amount);
        if ($amount<=0) {
            return ['errcode' => 10201,];
        }

        if (self::checkparam($params,"sign")==false) {
            return ['errcode' => 10201,];
        }

        //TODO:开始操作程序内部操作
        Log::var_dump("提款完成");

        return 'SUCCESS';
    }

    /**
     * 获取提现 key
     * @param $curr
     * @return string
     */
    private static function getwithdrawkey($curr) {
        // $curr = usdt, eoc ...
        $GAMENAME =  $GLOBALS['GAMENAME'];
        $key = $GAMENAME . ":withdraw:" . $curr;
        return $key;
    }

    /**
     * 用户提现
     * @param $request
     * @param $response
     * @param $rediscli
     * @return array
     */
    public static function withdraw($request, $response, $rediscli) {
        $uid = $request->getInt('uid');
        $amount = $request->getString('amount');
        $symbol = $request->getString("symbol");
        $address = $request->getString("address");
        $jiaoyipassword = $request->getString("jiaoyipassword");
        $codegoogle = $request->getString("codegoogle");
        if (empty($uid) || !isset($amount) || !isset($symbol) || !isset($address) || !isset($jiaoyipassword) || !isset($codegoogle)) {
            return ['errcode' => 10201,];
        }
        if (empty($address)) {
            return ['errcode'=> 10125,];
        }
        Log::var_dump($amount);
        $amount = floatval($amount);
        if (empty($amount) || $amount == 0) {
            return ['errcode' => 10201,];
        }
        //TODO 验证Google
        $ga = new GoogleAuthenticator(); 
        $secret = $u->googlekey;
        $oneCode = $ga->getCode($secret); //服务端计算"一次性验证码",暂时用不上
        // 最后一个参数 为容差时间,这里是2 那么就是 2* 30 sec 一分钟.
        $checkResult = $ga->verifyCode($secret, $codegoogle, 2);
        if (!$checkResult) {
            return ["errcode"=>10123,];
        };

        $u = UserLogic::loaduser($rediscli, $uid);
        if ($u->id == 0) {
            return ["errcode" => 10006,];
        }
        if ($u->accountstatus == 1) {
            return ["errcode" => 10008,];
        }
        if ($u->jiaoyipassword != $jiaoyipassword) {
            return ['errcode'=> 10130,];
        }

        //手续费
        $cost = config::get('tixian', 'sxf');
        Log::var_dump($cost);
        if (strtoupper($symbol)=="USDT") {
            if ($u->toparam()['usdt'] < ($amount+$cost)) {
                return ['errcode' => 10205,];
            } 
            $res = $u->xiausdt($amount+$cost);
            Log::var_dump($res);

            if ($res != 'ok') {
                return ['errcode' => 10213,];
            }
        }
        if (strtoupper($symbol)=="BSP") {
            if ($u->toparam()['bsp'] < ($amount+$cost)) {
                return ['errcode' => 10205,];
            } 
            $res = $u->xiabsp($amount+$cost);
            Log::var_dump($res);

            if ($res != 'ok') {
                return ['errcode' => 10213,];
            }
        }

        $u->load();
        QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());
        
        //生成提现ID
        $id = GenID::genid($rediscli,10000000001,99999999999,function($c){
            return self::getwithdrawkey($c);
        },"id");

        $key=KeysUtil::get_model_key('withdrawinfo',$id);
        //创建提现模型
        $m=new WithdrawModel($key,$rediscli);
        $m->load();
        $m->amount     = $amount;
        $m->symbol     = $symbol;
        $m->address    = $address;
        $m->uid        = $uid;
        $m->status     = 0;
        $m->orderid    = $id;
        $m->save();
        $m->load();

        // TODO:插入提现记录，mysql
        $withdraw = CoreConfig::get("withdraw");
        QueueHelper::putLog($withdraw,"wallet","withdrawrecord",$m->toparam());

        $u->load();
        return ['errcode' => 0, 'data' => ['user' => $u->toparam()],];
    }


    /**
     * 设置提现地址
     * @param $request
     * @param $response
     * @param $rediscli
     * @return array
     */
    public static function setcashaddr($request, $response, $rediscli) {
        $uid    = $request->getInt('uid');
        $caddr  = $request->getString('caddr');

        if (empty($uid) || !isset($caddr)) {
            return ['errcode' => 10201,];
        }

        $u = UserLogic::loaduser($rediscli, $uid);
        if ($u->id == 0) {
            return ["errcode" => 10006,];
        }
        if ($u->accountstatus == 1) {
            return ["errcode" => 10008,];
        }

        $u->caddr = $caddr;
        $u->save();

        return ['errcode' => 0, 'data' => ['user' => $u->toparam(),],];
    }
    /**
     * 后台处理用户提现
     * @param $request
     * @param $response
     * @param $rediscli
     * @return array
     */
    public static function completewithdraw($request, $response, $rediscli) {
        $uid    = $request->getInt('uid');
        $amount   = $request->getString('amount');
        $symbol = $request->getString('symbol');
        $status = $request->getInt('status');
        $address = $request->getString('address');
        $orderid = $request->getString('orderid');

        if (empty($uid) || !isset($amount) || !isset($symbol) || empty($status) || empty($address) || empty($orderid)) {
            return ['errcode' => 10201,];
        }

        $amount = floatval($amount);
        if (empty($amount) || $amount == 0) {
            return ['errcode' => 10201,];
        }

        $u = UserLogic::loaduser($rediscli, $uid);
        if ($u->id == 0) {
            return ["errcode" => 10006,];
        }
        if ($u->accountstatus == 1) {
            return ["errcode" => 10008,];
        }

        //查询订单
        $key=KeysUtil::get_model_key('withdrawinfo',$orderid);
        $m=new WithdrawModel($key,$rediscli);
        $m->load();
        if ($m->uid!=$uid || $m->address!=$address) {
            return ["errcode" => 10207,];
        }
        //差值在1范围内
        if (abs($m->amount-$amount)>1) {
            return ["errcode" => 10207,];
        }

        $data = $m->toparam();
        if ($status==2) { //拒绝，需要返还
            $cost = config::get('tixian', 'sxf');
            if (strtoupper($symbol)=="USDT") {
                $u->shangusdt($cost+$amount);
            }
            if (strtoupper($symbol)=="BSP") {
                $u->shangbsp($cost+$amount);
            }
            $u->load();
            $m->release();
            QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());
        }elseif($status==1){
            $usdtresult = WalletLogic::tibi(strtoupper($symbol),$m->address,$m->amount);
            if ($usdtresult==false) {
                return ["errcode"=>10303,];
            }else{
                if ($usdtresult["errcode"]!=0) {
                    return ["errcode"=>10303,"usdtresult"=>$usdtresult];
                }
            }
            $m->release();
            Log::var_dump($usdtresult);
        }

        return ['errcode' => 0, 'data' => ['user' => $u->toparam()],];
    }


    //获得充值记录
    public static function getrechargelist($request,$response,$rediscli){
        $index = $request->getInt("index"); // 下标
        $count = $request->getInt("count"); // 从index开始，往前多少条
        if(empty($index)){
            $index=0;
        }
        if(empty($count)){
            $count=1;
        }
        $uid = $request->getInt("uid");

        //TODO:去mysql里面查，领取红包的时候，要包领取的操作保存到mysql数据库中
        $mysql = Mysql::getInstance("mysql1");
        $sql = <<<crifan
        select * from (SELECT @rowno:=@rowno+1 as rowno,r.* from (SELECT * FROM `s_rechargerecord_log` WHERE `uid`=$uid order by createtime desc) r,(select @rowno:=0) t) as P where P.rowno LIMIT $index,$count;
crifan;
        $res = $mysql->query($sql);
        $records=array();
        $records['list']= $res->fetch_all(MYSQLI_ASSOC);

        // 获得总个数
        $sql1 = <<<crifan
        SELECT count(*) as totalcount FROM `s_rechargerecord_log` WHERE `uid`=$uid;
crifan;
        $res1 = $mysql->query($sql1);
        $ressx = $res1->fetch_assoc();
        $records['total'] = $ressx['totalcount'] == '' ?0 :$ressx['totalcount'];

        $u = UserLogic::loaduser($rediscli,$uid);

        return ['errcode'=>0,"data"=>["user"=>$u->toparam(),"records"=>$records]];
    }

    //获得提现记录
    public static function getwithdrawlist($request,$response,$rediscli){
        $index = $request->getInt("index"); // 下标
        $count = $request->getInt("count"); // 从index开始，往前多少条
        if(empty($index)){
            $index=0;
        }
        if(empty($count)){
            $count=1;
        }
        $uid = $request->getInt("uid");
        Log::var_dump($uid);

        //TODO:去mysql里面查，领取红包的时候，要包领取的操作保存到mysql数据库中
        $mysql = Mysql::getInstance("mysql1");
        $sql = <<<crifan
        select * from (SELECT @rowno:=@rowno+1 as rowno,r.* from (SELECT * FROM `s_withdrawrecord_log` WHERE `uid`=$uid order by createtime desc) r,(select @rowno:=0) t) as P where P.rowno LIMIT $index,$count;
crifan;
        $res = $mysql->query($sql);
        $records=array();
        $records['list']= $res->fetch_all(MYSQLI_ASSOC);

        // 获得总个数
        $sql1 = <<<crifan
        SELECT count(*) as totalcount FROM `s_withdrawrecord_log` WHERE `uid`=$uid;
crifan;
        $res1 = $mysql->query($sql1);
        $ressx = $res1->fetch_assoc();
        $records['total'] = $ressx['totalcount'] == '' ?0 :$ressx['totalcount'];

        $u = UserLogic::loaduser($rediscli,$uid);

        return ['errcode'=>0,"data"=>["user"=>$u->toparam(),"records"=>$records]];
    }
}

class WalletCtrl {

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
                $result = CMDWALLET::$fname($request,$response,$rediscli);
            }
        }else{
            $result = CMDWALLET::$fname($request,$response,$rediscli);
        }

        if($result['errcode']!=0){
            $errcode = $result['errcode'];
            $result['errmsg']=Config::get('errmsg','e'.$errcode);
            $result['errmsgen']=Config::get('errmsgen','e'.$errcode);
        }

        Log::info('debug',$request,['fname'=>$fname,'result'=>$result]);
        $response->say(json_encode($result));
    }

    public function invoke1($request,$response,$params)
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
                $result = CMDWALLET::$fname($request,$response,$rediscli);
            }
        }else{
            $result = CMDWALLET::$fname($request,$response,$rediscli);
        }

        if($result['errcode']!=0){
            $errcode = $result['errcode'];
            $result['errmsg']=Config::get('errmsg','e'.$errcode);
            $result['errmsgen']=Config::get('errmsgen','e'.$errcode);
            $response->say(json_encode($result)); 
        }else{
            Log::info('debug',$request,['fname'=>$fname,'result'=>$result]);
            $response->say($result);
        }
    }
}