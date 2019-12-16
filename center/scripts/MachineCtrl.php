<?php
namespace scripts;

use logic\AllShouyiLogic;
use logic\ShouYiLogic;
use QYS\Protocol\Response;
use QYS\Protocol\Request;
use QYS\Db\Mysql;
use QYS\Db\Redis;
use QYS\Log\Log;
use globalunit\utils\Config;
use globalunit\utils\KeysUtil;
use globalunit\utils\MyLocker;
use globalunit\utils\QueueHelper;
use globalunit\logic\UserLogic;
use globalunit\logic\TuiJianLogic;
use globalunit\logic\RedisLogic;
use logic\MachineLogic;
use Carbon\Carbon;
use utils\GoogleAuthenticator;

class MACHINECMD {

    //获得时光机列表
    public static function getmachineshop($request, $response, $rediscli) {
        $uid    = $request->getInt('uid');

        if (empty($uid)) {
            return ['errcode' => 10201,];
        }

        $machinelist = array();
        $machines = Config::get('machines_price');
        for ($i=0; $i < count($machines); $i++) { 
            $output = Config::get('machines_output')[$i];
            $through = $output/Config::get('machines_through',"price");
            array_push($machinelist, ["type"=>$i+1,"price"=>$machines[$i],"output"=>$output,"through"=>$through]);
        }

        //穿梭力排名列表100名
        $mysql = Mysql::getInstance("mysql1");
        $sql = <<<crifan
        SELECT * FROM s_member ORDER BY through DESC limit 10;
crifan;
        $res = $mysql->query($sql);
        $res = $res->fetch_all(MYSQLI_ASSOC);

        return ['errcode' => 0, 'data' => [ 'machinelist' => $machinelist,'ranklist'=>$res],];
    }

    //购买时光机
    public static function machinebuy($request, $response, $rediscli) {
        $uid    = $request->getInt('uid');
        $type  = $request->getInt('type');

        if (empty($uid) || !isset($type)) {
            return ['errcode' => 10201,];
        }

        $u = UserLogic::loaduser($rediscli, $uid);
        if ($u->id == 0) {
            return ["errcode" => 10006,];
        }
        if ($u->accountstatus == 1) {
            return ["errcode" => 10008,];
        }

        $price = Config::get('machines_price')[$type-1];
        if ($u->usdt < $price) {
            return ['errcode' => 10205,];
        }

        $res = $u->xiausdt($price);
        if ($res != 'ok') {
            return ['errcode' => 10203,];
        }
        
        $data = MachineLogic::createMachine($rediscli, $uid, $price);
        $data['status'] = 0;
        // 下日志
        QueueHelper::putLog($data['id'],"machine","machinelist",$data);

        //首次购买时光机，送穿梭力
        $percent = Config::get('first_machine_through')[$price];
        $through = $price/Config::get('machines_through',"usdt2through");
        $get = $percent*$through;
        $u->shangthrough($get);
        $u->shangdaythrough($get);
        $log2 = [
            'uid'           => $uid,
            'bsp'           => 0,
            'through'       => $get,
            'type'          => 3,
            'machineid'     => $data['id'],
            'createtime'    => time(),
        ];
        QueueHelper::putLog(time().$uid, 'machine', 'machinelog', $log2);

        //上级的二次购买，返利上级
        if ($u->shangji>0 && $u->shangji!=123456789) {
            $suid = $u->shangji;
            $suser = UserLogic::loaduser($rediscli, $suid);
            $keylist=MachineLogic::getListKey($suid);
            $res = $rediscli->zrange($keylist,0,-1,True) or [];
            foreach($res as $machineid=>$index){
                $item=MachineLogic::loadMachine($rediscli,$machineid);
                $price = $item->price;
                $percent = Config::get('second_machine_through')[$price];
                $through = $price/Config::get('machines_through',"usdt2through");
                $get = $percent*$through;
                $suser->shangthrough($get);
                $suser->shangdaythrough($get);
                $log3 = [
                    'uid'           => $suid,
                    'bsp'           => 0,
                    'through'       => $get,
                    'type'          => 4,
                    'machineid'     => $item->id,
                    'fromuid'     => $uid,
                    'createtime'    => time(),
                ];
                QueueHelper::putLog(time().$suid, 'machine', 'machinelog', $log3);
            }
        }
        //给上级返利
        MachineLogic::releaseSuper($rediscli,$uid,$price,$item->id);

        $u->load();
        QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());

        return ['errcode' => 0, 'data' => ['user' => $u->toparam(), 'machine' => $data],];
    }

    //销毁时光机
    public static function machinedel($request, $response, $rediscli) {
        $uid    = $request->getInt('uid');
        $machineid  = $request->getInt('machineid');

        if (empty($uid) || empty($machineid)) {
            return ['errcode' => 10201,];
        }

        $u = UserLogic::loaduser($rediscli ,$uid);
        if ($u->id == 0) {
            return ["errcode" => 10006,];
        }
        if ($u->accountstatus == 1) {
            return ["errcode" => 10008,];
        }

        $i = MachineLogic::loadMachine($rediscli, $machineid);
        if ($i->id==0) {
            return ["errcode" => 10207,];
        }

        $u->shangusdt($i->price);
        $u->shangbsp($i->pledge);
        $u->load();
        QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());

        $data = $i->toparam();
        $data['status']     = 1;
        // 撤销时光机修改库状态
        QueueHelper::putLog($i->id,"machine","machinelist",$data);

        // 清理当前用户报单列表中该条报单记录
        MachineLogic::delMachine($rediscli,$uid,$machineid);

        return ['errcode' => 0, 'data' => ['user' => $u->toparam()]];
    }

    //升级时光机
    public static function machineupgrade($request, $response, $rediscli) {
        $uid    = $request->getInt('uid');
        $machineid  = $request->getInt('machineid');
        $price  = $request->getInt('price');

        if (empty($uid) || empty($machineid) || empty($price)) {
            return ['errcode' => 10201,];
        }

        if ($price<=0) {
            return ['errcode' => 10201,];
        }

        $u = UserLogic::loaduser($rediscli ,$uid);
        if ($u->id == 0) {
            return ["errcode" => 10006,];
        }
        if ($u->accountstatus == 1) {
            return ["errcode" => 10008,];
        }

        $i = MachineLogic::loadMachine($rediscli, $machineid);
        if ($i->id==0) {
            return ["errcode" => 10207,];
        }
        
        if ($price<=$i->price) {
            return ['errcode' => 10129,];
        }

        $needprice = $price-$i->price;
        if ($u->usdt < $needprice) {
            return ['errcode' => 10205,];
        }

        $res = $u->xiausdt($needprice);
        if ($res != 'ok') {
            return ['errcode' => 10203,];
        }
        $i->price = $price;
        $i->save();
        $i->load();

        $u->load();
        QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());

        $data = $i->toparam();
        $data['status'] = 0;
        // 升级时光机修改库状态
        QueueHelper::putLog($i->id,"machine","machinelist",$data);

        return ['errcode' => 0, 'data' => ['user' => $u->toparam(),'machine'=>$i->toparam()]];
    }

    //质押
    public static function machinepledge($request, $response, $rediscli) {
        $uid    = $request->getInt('uid');
        $machineid  = $request->getInt('machineid');
        $count  = $request->getInt('count');

        if (empty($uid) || empty($machineid) || empty($count)) {
            return ['errcode' => 10201,];
        }

        if ($count==0) {
            return ['errcode' => 10201,];
        }

        $u = UserLogic::loaduser($rediscli ,$uid);
        if ($u->id == 0) {
            return ["errcode" => 10006,];
        }
        if ($u->accountstatus == 1) {
            return ["errcode" => 10008,];
        }

        $i = MachineLogic::loadMachine($rediscli, $machineid);
        if ($i->id==0) {
            return ["errcode" => 10207,];
        }

        //需要对质押数量做限制
        $max = config::get('captain', "max");
        if ($u->through<=$max && ($i->pledge+$count)>500) {
            return ['errcode' => 10128,];
        }
        //初级100
        if ($i->price==100 && ($i->pledge+$count)>100) {
            return ['errcode' => 10128,];
        }
        //zhong级500
        if ($i->price==500 && ($i->pledge+$count)>300) {
            return ['errcode' => 10128,];
        }

        //质押和撤销质押
        if ($count>0) {
            if ($u->bsp < $count) {
                return ['errcode' => 10205,];
            }

            //重新启动扣除重启费用
            $cost = 0;
            if ($i->pledge<=0 && $i->activate==false) {
                $i->activate=true;
            }elseif ($i->pledge<=0 && $i->activate==true) {
                $percent = Config::get('machines_through',"restartcost");
                $cost = $i->price*$percent;
                if ($u->toparam()['usdt']<$cost) {
                    return ['errcode' => 10131,];
                }
            }

            $res = $u->xiabsp($count);
            if ($res != 'ok') {
                return ['errcode' => 10203,];
            } 
            if ($cost>0) {
                Log::var_dump("重启时光机消耗：".$cost);
                $res = $u->xiausdt($cost);
                if ($res != 'ok') {
                    return ['errcode' => 10203,];
                } 
            }
        }else{
            if (abs($count)>$i->pledge) {
                return ['errcode' => 10203,];
            } 
            $res = $u->shangbsp(-$count);
        }

        $i->pledge += $count;
        $i->save();
        $i->load();

        $u->load();
        QueueHelper::putLog($u->id.time(), 'user', 'userinfo', $u->toparam());

        $data = $i->toparam();
        $data['status'] = 0;
        // 升级时光机修改库状态
        QueueHelper::putLog($i->id,"machine","machinelist",$data);

        return ['errcode' => 0, 'data' => ['user' => $u->toparam(),'machine'=>$i->toparam()]];
    }

    //获得买入列表
    public static function getmachinelist($request, $response, $rediscli) {
        $index = $request->getInt("index"); // 下标
        $count = $request->getInt("count"); // 从index开始，往前多少条
        $uid    = $request->getInt('uid');

        if (empty($uid)) {
            return ['errcode' => 10201,];
        }

        $u = UserLogic::loaduser($rediscli ,$uid);
        if ($u->id == 0) {
            return ["errcode" => 10006,];
        }
        if ($u->accountstatus == 1) {
            return ["errcode" => 10008,];
        }

        $machinelist = MachineLogic::getmachinelist($rediscli,$index,$count,$uid);

        return ['errcode' => 0, 'data' => ['user' => $u->toparam(),"machinelist"=>$machinelist,]];
    }
}

class MachineCtrl {
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
                $result = MACHINECMD::$fname($request,$response,$rediscli);
            }
        }else{
            $result = MACHINECMD::$fname($request,$response,$rediscli);
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