<?php
namespace logic;
use globalunit\model\UserModel;
use globalunit\utils\KeysUtil;
use QYS\Util\Debug;
use globalunit\utils\GenID;
use globalunit\utils\RndName;
use globalunit\utils\Config;
use QYS\third\Crypto\XXTEA;
use QYS\Core\Config as CoreConfig;
use globalunit\utils\QueueHelper;
use QYS\Log\Log;

Class WalletLogic{
    public static function curlsend($url, $post_data) {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
              'method' => 'POST',
              'header' => 'Content-type:application/x-www-form-urlencoded',
              'content' => $postdata,
              'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    public static function encodeparam($params) {
        $keys=array();
        foreach ($params as $key => $value) {
            array_push($keys, $key);
        }
        sort($keys);

        $tmp = array();
        for ($i=0; $i < count($keys); $i++) { 
            $v = $params[$keys[$i]];
            array_push($tmp, "$keys[$i]=$v");
        }
        $encodeparams = join("&",$tmp);
        $sign = strtoupper(md5($encodeparams));
        return $sign;
    }

    public static function checkparam($params, $signame) {
        $cloneparam = $params;
        unset($cloneparam[$signame]);
        $keys=array();
        foreach ($params as $key => $value) {
            array_push($keys, $key);
        }
        sort($keys);

        $tmp = array();
        for ($i=0; $i < count($keys); $i++) { 
            $v = $params[$keys[$i]];
            array_push($tmp, "$keys[$i]=$v");
        }

        $encodeparams = join("&",$tmp);
        $sign = strtoupper(md5($encodeparams));
        return $sign == strtoupper($params[$signame]);
    }

    // 创建钱包.
    public static function getAddress(){
        $tip = Config::get('WALLET_SERVER_CONFIG', 'ip');
        $tport = Config::get('WALLET_SERVER_CONFIG', 'port');
        $url = 'http://'.$tip.':'.$tport.'/api/GenerateAddress';
        $res = self::curlsend($url,[]);
        $res = json_decode($res, true);
        return $res;
    }


    // 提币
    public static function tibi($symbol,$address,$orderAmount) {
        $signType   = "MD5";
        $tmp        = [];
        $tmp["signType"]    = strval($signType);
        $tmp["address"]      = strval($address);
        $tmp["symbol"]      = strval($symbol);
        $tmp["merchantid"]  = strval(Config::get('WALLET_SERVER_CONFIG', 'merchantid'));
        $tmp["orderAmount"]      = strval($orderAmount);
        $tmp["merchantkey"] = strval(Config::get('WALLET_SERVER_CONFIG', 'merchantkey'));
        $md5info_tem = self::encodeparam($tmp);

        $tip = Config::get('WALLET_SERVER_CONFIG', 'ip');
        $tport = Config::get('WALLET_SERVER_CONFIG', 'port');
        $url = 'http://'.$tip.':'.$tport.'/api/CashWithdrawal';
        Log::var_dump($url);

        $data = array(
            "signType"=>$signType,
            "address"=>$address,
            "symbol"=>$symbol,
            "merchantid"=>$tmp["merchantid"],
            "orderAmount"=>$orderAmount,
            "sign"=>$md5info_tem,
        );

        Log::var_dump($data);
        $jieguodata = self::curlsend($url,$data);
        if (isset($jieguodata)) {
            return json_decode($jieguodata,true);
        }
        return false;
    }
}
