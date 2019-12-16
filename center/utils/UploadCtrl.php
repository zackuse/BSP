<?php
/**
 * Created by PhpStorm.
 * User: zcw 812039610@qq.com
 * Date: 2019/7/9
 * Time: 11:30
 */
namespace scripts;

use globalunit\utils\AliOSS;
use QYS\Protocol\Response;
use QYS\Protocol\Request;
use QYS\Db\Mysql;
use QYS\Db\Redis;
use QYS\Log\Log;
use globalunit\utils\config;
use globalunit\utils\KeysUtil;
use globalunit\utils\MyLocker;
use QYS\Core\Config as CoreConfig;
use utils\OssUtil;

class CMDUPLOAD {

    public static function upload($request,$response,$rediscli){
        $imgData = $request->postString('info');
        if(!$imgData){
            return ['errcode' => 10001];
        }

        $imgData = str_replace('\n','',$imgData);
        $imgData = str_replace('\r','',$imgData);
        $imgData = str_replace('\t','',$imgData);
        $imgData = str_replace(PHP_EOL,'',$imgData);
        $imgData = str_replace('\\','',$imgData);
        $imgData = str_replace('','+',$imgData);
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $imgData, $result)){
            Log::var_dump(array('result'=>$result));
            $type = $result[2];
            $img = base64_decode(str_replace($result[1], '', $imgData));
        }

        $config = [
            'bucket' => CoreConfig::get('bucket'),
            'endpoint' => CoreConfig::get('Endpoint'),
            'accessKey' => CoreConfig::get('AccessKeyId'),
            'secretKey' => CoreConfig::get('AccessKeySecret'),
        ];
        $date = 'image';
        $uniqid_name = md5(uniqid(rand()));
        $object = $date.'/avatar_'.$uniqid_name.'.png';
        $cli = new AliOSS($config);
        $res = $cli->put_object($img,"image/png",$object);
        if($res != 200){
            return ['errcode' => 90007];
        }
        $img_src = 'http://'.CoreConfig::get('oss_url').'/'.$object;
        return ['errcode'=>0, 'src'=> $img_src];
    }

}

class UploadCtrl {

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
                $result = CMDUPLOAD::$fname($request,$response,$rediscli);
            }
        }else{
            $result = CMDUPLOAD::$fname($request,$response,$rediscli);
        }

        if(isset($result['errcode']) && $result['errcode']!=0){
            $errcode = $result['errcode'];
            $result['errmsg']=Config::get('errmsg','e'.$errcode);
        }

        Log::info('debug',$request,['fname'=>$fname,'result'=>$result]);
        $response->say(json_encode($result));
    }

}
