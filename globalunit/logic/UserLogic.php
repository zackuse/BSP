<?php
namespace globalunit\logic;
use globalunit\model\UserModel;
use globalunit\utils\KeysUtil;
use QYS\Util\Debug;
use globalunit\utils\GenID;
use globalunit\utils\RndName;
use QYS\third\Crypto\XXTEA;
use QYS\Core\Config as CoreConfig;
use globalunit\utils\Config;
use globalunit\utils\QueueHelper;
use QYS\Log\Log;
use globalunit\logic\TuiJianLogic;
use logic\MachineLogic;

function strToHex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strtolower($hex);
}

Class UserLogic{

    public static function loaduser($rediscli,$uid){
        $key=KeysUtil::get_user_main_key($uid);
        $u=new UserModel($key,$rediscli);
        $u->load();
        return $u;
    }
    
    public static function register($rediscli,$data){
        $uid=GenID::genid($rediscli,100001,999999,function($c){
            return KeysUtil::get_user_main_key($c);
        },"id");
        $u=self::loaduser($rediscli,$uid);
        $u->id=$uid;
        $u->phone=$data['phone'];
        $u->password=$data['password'];
        $u->jiaoyipassword=$data['jiaoyipwd'];
        $u->address=$data['address'];
        $u->nickname=$data['nickname'];
        $u->createtime=time();
        $str = "$uid";
        $key = "nihaoma";
        $encrypt_data = XXTEA::encrypt($str, $key);
        $u->xxtkey=strToHex($encrypt_data);
        $u->save();

        // 手机号占位
        $mapkey = KeysUtil::get_main_usermap($data['phone']);
        $rediscli->set($mapkey,$uid);
        
        return $u->toparam();
    }

    public static function login($code){
        $res = array();
        $res["unionid"] = $code;
        $res["nickname"] = $code;
        $res["avatar"] = 'https://bspkuangjivdshot.oss-cn-beijing.aliyuncs.com/bg_touxaing.png';
        $res["sex"] = 1;
        $res["openid"] = $code;

        return $res;
    }

    public static function wxlogin($code){
        $res = array();
        //TODO appid 等放到配置里
        $APPID=CoreConfig::get("APPID");
        $SECRET=CoreConfig::get("SECRET");
        Log::var_dump($APPID);

        $url1 = sprintf("https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code",$APPID,$SECRET,$code);
        $source = file_get_contents($url1);
        $tmp1 = json_decode($source,true);

        Log::var_dump($tmp1);

        if ($tmp1["errcode"]!=0) {
            return $res;
        }

        $msg1 = json_decode($tmp1["body"],true);
        $access_token = $msg1["access_token"];
        $weixinopenid = $msg1["openid"];

        $url2 = sprintf("https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s",$access_token,$weixinopenid);
        $source = file_get_contents($url2);
        $tmp2 = json_decode($source,true);

        if ($tmp2["errcode"]!=0) {
            return $res;
        }

        $msg2 = json_decode($tmp2["body"],true);
        $res["unionid"] = $msg2["unionid"];
        $res["nickname"] = $msg2["nickname"];
        $res["avatar"] = $msg2["headimgurl"];
        $res["sex"] = $msg2["sex"];
        $res["openid"] = $weixinopenid;

        return $res;
    }

    //判断用户等级 0普通会员,1合伙人（购买时光机）。2超级舰长。3顶级账号
    public static function getuserlvl($rediscli,$uid){
        $u = self::loaduser($rediscli,$uid);
        $lvl = $u->userlvl;

        $max = Config::get('captain', "max");
        $machinecount = Config::get('captain', "machinecount");

        $keyuser=MachineLogic::getListKey($uid);
        $count = $rediscli->zcard($keyuser);
        if ($count>0) {
            $lvl = max($lvl,1);
        }

        if ($u->through>=$max) {
            $subsuper = 0; //直推的超级舰长
            $fanslvl1 = TuiJianLogic::xiajilistlevel($rediscli,$uid,1);
            for ($i=0; $i < count($fanslvl1); $i++) { 
                $uidlvl1 = $fanslvl1[$i]["uid"];
                $userlvl1 = UserLogic::loaduser($rediscli, $uidlvl1);

                $keylist=MachineLogic::getListKey($uidlvl1);
                $res = $rediscli->zrange($keylist,0,-1,True) or [];
                foreach($res as $machineid=>$index){
                    $item=MachineLogic::loadMachine($rediscli,$machineid);
                    $price = $item->price;
                    if ($price>=1000) {
                        $subsuper += 1;
                    }
                }
            }
            if ($subsuper>=$machinecount) {
                $lvl = max($lvl,2);
            }
        }

        return $lvl;
    }
}
