<?php
namespace globalunit\logic;
use globalunit\utils\KeysUtil;
use QYS\Util\Debug;
use globalunit\utils\GenID;
use globalunit\utils\RndName;
use QYS\third\Crypto\XXTEA;
use QYS\Core\Config;
use Carbon\Carbon;
use QYS\Log\Log;
use globalunit\logic\UserLogic;
use QYS\Db\Mongo;

Class TuiJianLogic{

    //添加推荐人
    public static function add($rediscli,$uid,$tuijianren,$createtime){
        assert(!empty($uid),"uid empty");
        assert(!empty($tuijianren),"tuijianren empty");
        $tuijianren = intval($tuijianren);
        $uid = intval($uid);
        $createtime = intval($createtime);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;

        $query = ["uid"=>$tuijianren];
        $res = $collection->findOne($query);

        $doc = array();

        if (empty($res)) {
            $res = array();
            $res["map"] = [];
            $res["list"] = [];
        }

        $map = array();
        $list = $res["list"];

        $doc["uid"] = $uid;
        $doc["createtime"] = $createtime;
        $doc["_id"] = $uid;

        $map["uid1"] = $tuijianren;

        for ($i=1; $i <= 1000000000000; $i++) {
            $k1 = "uid$i";
            $k2 = $i+1;
            $k2 = "uid$k2";
            $remmap = $res["map"];
            if (empty($remmap[$k1])) {
                break;
            }

            $map[$k2] = $remmap[$k1];
        }
        $list = (array)$list;
        array_unshift($list, $tuijianren);

        $doc["map"] = $map;
        $doc["list"] = $list;

        $collection->insertOne($doc);
    }

    //获取几级下级
    public static function xiajilistlevel($rediscli,$uid,$lvl){
        assert(!empty($uid),"uid empty");
        assert(!empty($lvl),"lvl empty");
        $lvl = intval($lvl);
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;

        $query = ["map.uid$lvl"=>$uid];
        $cursor=$collection->find($query);
        $tmp=array();
        if(isset($cursor)){
            foreach($cursor as $key1=>$value1){
                array_push($tmp, ["uid"=>$value1->uid]);
            }
        }
        // Log::var_dump($tmp);
        return $tmp;
    }


    public static function addyeji($rediscli,$uid,$yeji)
    {
        assert(!empty($uid),"uid empty");
        assert(!empty($yeji),"yeji empty");

        $yeji = intval($yeji);
        $uid  = intval($uid);

        $conn       = Mongo::getInstance('mongo1');
        $gamename   = $GLOBALS['GAMENAME'];
        $db         = $conn->$gamename;
        $collection = $db->tuijianren;

        $collection->updateOne(["_id"=>$uid,],['$inc'=>["yeji"=>$yeji]]);
    }

    public static function addyouxiao($rediscli,$uid,$youxiao)
    {
        assert(!empty($uid),"uid empty");
        assert(!empty($youxiao),"youxiao empty");

        $youxiao = intval($youxiao);
        $uid  = intval($uid);

        $conn       = Mongo::getInstance('mongo1');
        $gamename   = $GLOBALS['GAMENAME'];
        $db         = $conn->$gamename;
        $collection = $db->tuijianren;

        $collection->updateOne(["_id"=>$uid,],['$inc'=>["youxiao"=>$youxiao]]);
    }

    //TODO:获得所有下级的个数
    public static function xiajicount($rediscli,$uid)
    {
        assert(!empty($uid),"uid empty");
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;

        $query = ["list"=>$uid];
        $count=$collection->count($query);
        return  $count;
    }

    //TODO:获得某天直推下级的个数
    public static function getlvl1daycount($rediscli,$uid,$daybegin)
    {
        assert(!empty($uid),"uid empty");
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;

        $query = ["map.uid1"=>$uid,"list"=>$uid,"createtime"=>['$gt'=>$daybegin],"createtime"=>['$lt'=>$daybegin+24*3600]];
        $count=$collection->count($query);
        return  $count;
    }

    //获得直推充值会员数
    public static function getvalidzhitui($rediscli,$uid)
    {
        assert(!empty($uid),"uid empty");
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;

        $query = ["map.uid1"=>$uid,"yeji"=>['$gt'=>0]];
        $count=$collection->count($query);
        return  $count;

    }

    //获得伞下充值会员数
    public static function getvalidsanxia($rediscli,$uid)
    {
        assert(!empty($uid),"uid empty");
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;

        $query = ["list"=>$uid,"yeji"=>['$gt'=>0]];
        $count=$collection->count($query);
        return  $count;
    }

    //获得伞下充值会员总业绩列表
    public static function getvalidsanxiayeji($rediscli,$uid)
    {
        assert(!empty($uid),"uid empty");
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;
        $pipeline = array(
            array(
                '$match' => array(
                    "list"=>$uid,"yeji"=>['$gt'=>0]
                )
            ),
        );
        $arraysx=$collection->aggregate($pipeline);
        return  $arraysx;
    }

    //获得伞下会员列表
    public static function getsanxialist($rediscli,$uid)
    {
        assert(!empty($uid),"uid empty");
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;
        $pipeline = array(
            array(
                '$match' => array(
                    "list"=>$uid,
                )
            ),
        );
        $arraysx=$collection->aggregate($pipeline);
        return  $arraysx;
    }

    //获得我的信息
    public static function getself($rediscli,$uid)
    {
        assert(!empty($uid),"uid empty");
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;

        $query = ["uid"=>$uid];
        $res=$collection->findOne($query);
        return  $res;
    }


    //获得伞下充值会员数有效
    public static function getvalidsanxiayouxiao($rediscli,$uid)
    {
        assert(!empty($uid),"uid empty");
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;

        $query = ["list"=>$uid,"youxiao"=>['$gt'=>0]];
        $count=$collection->count($query);
        return  $count;
    }
    
    //获得我的所有上级
    public static function shangjilist($rediscli,$uid){
        assert(!empty($uid),"uid empty");
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;

        $query = ['uid'=>$uid];
        $res = $collection->findOne($query);

        if (empty($res)) {
            $res = array();
        }

        $tmp = array();

        if (!empty($res["map"])) {
            $ls = $res["map"];
            foreach ($ls as $key => $value) {
                $lvl = substr($key, 3, strlen($key)-3);
                array_push($tmp, ['level'=>$lvl,'uid'=>$value]);
            }

            usort($tmp,function($a,$b){
                return $a["level"]-$b["level"];
            });
        }

        return $tmp;
    }

    public static function getmyline($rediscli,$uid){
        $shangji = self::xiajilist($rediscli,$uid);
        $xiaji = self::shangjilist($rediscli,$uid);

        $tmp = array();

        foreach ($shangji as $key => $value) {
            array_push($tmp, $value);
        }

        foreach ($xiaji as $key => $value) {
            array_push($tmp, ['level'=>$value['level'],'uid'=>$value['uid']]);
        }

        array_push($tmp, ['level'=>0,'uid'=>$uid]);

        usort($tmp,function($a,$b){
            return $a["level"]-$b["level"];
        });

        return $tmp;
    }

    //获得兄弟会员的函数
    public static function getsibling($rediscli,$uid){
        assert(!empty($uid),"uid empty");
        $uid = intval($uid);

        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->tuijianren;

        $query = ["uid"=>$uid];
        $res=$collection->findOne($query);
        Log::var_dump($res);

        if (empty($res)) {
            $res = array();
        }

        $tmp=array();
        if (!empty($res['map'])) {
            $uid1 = $res['map']['uid1'];
            $query = ["map.uid1"=>$uid1];
            $cursor=$collection->find($query);

            if(isset($cursor)){
                foreach($cursor as $key1=>$value1){
                    if ($value1->uid != $uid) {
                        array_push($tmp, $value1->uid);
                    }
                }
            }
        }

        return $tmp;
    }
}
