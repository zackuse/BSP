<?php
namespace scripts;
use QYS\Protocol\Response;
use QYS\Protocol\Request;
use QYS\Task\Factory;
use QYS\Db\Mysql;
use QYS\Util\Debug;
use QYS\Log\Log;
use QYS\Db\Redis;

use globalunit\utils\DBHelper;
use globalunit\utils\Config;
use globalunit\utils\JWTUtil;
use globalunit\utils\RequestHelper;
use globalunit\utils\Utils;
use globalunit\utils\KeysUtil;
use globalunit\utils\Common;
use QYS\Core\Config as QYSConfig;
use QYS\QYS;
class Access
{
    public function access($request,$response,$params)
    {
        $response->addHeader("Content-Type", 'application/json');
        $response->addHeader("Access-Control-Allow-Origin", "*");
        $response->addHeader("Access-Control-Allow-Methods", 'POST, GET, OPTIONS, DELETE');
        $response->addHeader('Access-Control-Allow-Headers', "Origin, X-Requested-With, Content-Type, Accept");
        $response->sendHttpHeader();

        $jwt=$request->getString("jwt");
        Log::info('debug',$request,['jwt'=>$jwt]);
        if(empty($jwt)){
            $response->addHeader("Content-Type", 'application/json');
            $response->sendHttpHeader();
            $result= array("errcode" => 401, "errmsg" =>"登录验证失败","errmsgen"=>"Login authentication failed");
            $response->say(json_encode($result ));
            $response->status(401);
            QYS::bye();
        }
        $GAMENAME=  $GLOBALS['GAMENAME'];
        $VERSION=  $GLOBALS['VERSION'];
        $jwtobj = JWTUtil::decode($jwt, "$GAMENAME-$VERSION", array('HS256'));
        if(empty($jwtobj) || empty($jwtobj->uid) || empty($jwtobj->token) ){
            $errcode = 1;
            $response->status(401);
            $response->say(json_encode(array("errcode"=>$errcode,"errmsg"=>'登录验证失败',"errmsgen"=>"Login authentication failed")));
            QYS::bye();
            return;
        }

        $uid=$jwtobj->uid;
        $token=$jwtobj->token;
        $rediscli = Redis::getInstance("redis1");
        $tokenkey = KeysUtil::get_main_token($uid);
        $token0=$rediscli->get($tokenkey);

        if($token0!=$token){
            $errcode = 2;
            $response->status(401);
            $response->say(json_encode(array("errcode"=>$errcode,"errmsg"=>"账户已经在其他终端上登录","errmsgen"=>"The account is already logged in on another terminal")));
            QYS::bye();
            return;
        }
        $request->getRequest()->get["uid"]=$uid;
    }

}
