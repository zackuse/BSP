<?php
/**
 * Created by PhpStorm.
 * User: chorkeung
 * Date: 2019/5/22
 * Time: 10:04 AM
 */
namespace utils;

use globalunit\utils\Config;
use globalunit\utils\QueueHelper;
use globalunit\logic\TuiJianLogic;
use globalunit\logic\UserLogic;
use QYS\Util\Debug;
use QYS\Core\Config as CoreConfig;
use QYS\Log\Log;


class SmsUtil{
    
    public static function smssend($phone, $type) {
        $sinfo = Config::get('sms_info');
        $code = rand(1000, 9999);

        switch($type){
            case 1:
                $content = "【BSP】您正在注册，您的验证码是：{$code}。如非本人操作，请忽略本短信。";
                break;
            case 2:
                $content = "【BSP】您正在修改密码，您的验证码是：{$code}。如非本人操作，请忽略本短信。";
                break;
        }
        $data = array(
            'userid'    => $sinfo['userid'],        // 企业ID.
            'account'   => $sinfo['account'],       // 发送用户账号.
            'password'  => md5($sinfo['password']), // 发送接口密码. 用md5加密方式，md5采用32位大写
            'mobile'    => "$phone",                // 全部被叫号码. 短信发送的目的号码. 多个号码之间用半角逗号隔开
            'content'   => "{$content}",            // 发送内容. 提交内容格式：内容+【签名】。签名是公司的名字或者公司项目名称。示例：您的您的验证码为：1439【腾飞】。
            'action'    => $sinfo['action'],
        );

        $res = self::curlsend($sinfo['api_url'], $data);
        $arr = json_decode($res,true);
        $arr['code'] = $code;
        return $arr;
    }

    //创锐短信
    public static function smssend_cr($phone) {
        $code = rand(1000, 9999);
        $data = array(
            "name"=>'17099425679',
            "pwd"=>'02197F407502B6BD63E3EED2FAA0',
            "content"=>"您的验证码为：{$code}，请在 5 分钟内输入。感谢您的支持，祝您生活愉快！",
            "mobile"=>$phone,
            "sign"=>'BSP',
            "type"=>'pt',
        );

        $res = self::curlsend("http://web.cr6868.com/asmx/smsservice.aspx", $data);
        $arr = json_decode($res,true);
        $arr['code'] = $code;
        return $arr;
    }

    //创锐短信国际
    public static function smssend_cr_inter($phone) {
        $code = rand(1000, 9999);
        $data = array(
            "accesskey"=>'17099425679',
            "secret"=>'02197F407502B6BD63E3EED2FAA0',
            "content"=>"您的验证码为：{$code}，请在 5 分钟内输入。感谢您的支持，祝您生活愉快！",
            "mobile"=>$phone,
            "sign"=>'【BSP】',
        );

        $res = self::curlsend("http://intlapi.1cloudsp.com/intl/api/v2/send", $data);
        $arr = json_decode($res,true);
        $arr['code'] = $code;
        return $arr;
    }

    public static function curlsend($url, $data = '', $type = 'POST') {
        if($type == 'POST') {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $data = curl_exec($curl);

            if ($data) {
                curl_close($curl);
                return $data;
            } else {
                $error=curl_errno($curl);
                curl_close($curl);
                return 'ERROR #'.$error;
            }
        } else {
            $data = file_get_contents($url);
        }
        return $data;
    }
}
