<?php
namespace globalunit\utils;


use \Swoole\Coroutine as co;
use QYS\QYS;
use QYS\Util\Dir;
use QYS\Core\Config;
use QYS\Db\Redis;

/**
 * 公用工具类
 */

Class Tools{

    /**
     * post和get请求模拟
     * @param string  $url         访问的URL
     * @param string $post         post数据(不填则为GET)
     * @param string $cookie       提交的$cookies
     * @param bool   $returnCookie 是否返回$cookies
     * @return mixed|string
     */
    public static function request($url, $post = '', $cookie = '', $returnCookie = false) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if ($returnCookie) {
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie'] = substr($matches[1][0], 1);
            $info['content'] = $body;
            //return json_decode($info, true);
            //$info = iconv('GB2312','UTF-8',$info);
            return $info;
        } else {
            //$data = iconv('GB2312','UTF-8',$data);
            // return json_decode($data, true);
            return $data;
        }
    }
    /**
     * post和get请求模拟
     * @param string  $url         访问的URL
     * @param string $data         post数据
     * @param string $type         提交的类型(不填则为POST)
     * @return mixed|string
     */
    public static function curl($url, $data = '', $type = 'POST') {
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
    //获取id号（唯一标示）
    public static function get_gen_id($redis, $key, $keys){
        $GAMENAME = $GLOBALS['GAMENAME'];
        $key = "$GAMENAME:$key:$keys";
        $cnt = $redis->incrby($key,1);
        return (int)$cnt;
    }

    /**
     * 生成不重复订单ID（并发下）
     * @param integer   $uid
     * @param string   $prefix
     * @param string   $name
     * @return string
     */
    public static function getOrderId($uid, $prefix = 'DD', $name = 'order')
    {
        return $prefix . (strtotime(date('YmdHis', time()))) . self::getNumberId($name) . $uid;
    }

    //获取订单号（唯一标示）
    public static function getNumberId($name = 'order')
    {
        $rediscli  = Redis::getInstance("redis1");
        $GAMENAME  = $GLOBALS['GAMENAME'];
        $key = "$GAMENAME:$name:number";
        $cnt = $rediscli->incrby($key,1);
        return $cnt;
    }
    /**
     * 手机号证验证
     * @param string   $phone
     * @return bool
     */
    public static function isPhone($phone){
        $g = "/^1[34578]\d{9}$/";
        $g2 = "/^19[89]\d{8}$/";
        $g3 = "/^166\d{8}$/";
        if(preg_match($g, $phone)){
            return true;
        }else  if(preg_match($g2, $phone)){
            return true;
        }else if(preg_match($g3, $phone)){
            return true;
        }
        return false;
    }

    /**
     * 身份证验证
     * @param string   $idcard
     * @return bool
     */
    public static function isIdCard($idcard) {
        // 只能是18位
        if(strlen($idcard)!=18){
            return false;
        }
        // 取出本体码
        $idcard_base = substr($idcard, 0, 17);
        // 取出校验码
        $verify_code = substr($idcard, 17, 1);
        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        // 校验码对应值
        $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        // 根据前17位计算校验码
        $total = 0;
        for($i=0; $i<17; $i++){
            $total += substr($idcard_base, $i, 1)*$factor[$i];
        }
        // 取模
        $mod = $total % 11;
        // 比较校验码
        if($verify_code == $verify_code_list[$mod]){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 中文姓名验证
     * @param string   $name
     * @return bool
     */
    public static function isChineseName($name){
        if (preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $name)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 钱包地址验证
     * @param string   $address
     * @return bool
     */
    public static function isAddress($address){
        $regex = '/^0x[a-fA-F0-9]{40}$/';
        if(preg_match($regex, $address)){
            return true;
        } else {
            return false;
        }
    }

    /**
     * 日志写入log文件
     * @param string  $type 文件名称
     * @param array   $params   写入文件的参数
     */
    public static function writeLog($type, $params = [])
    {
        $separator = "\t";
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        $t = \date("Ymd");
        $logPath = Config::getField('project', 'log_path', '');
        if (empty($logPath)) {
            $dir = QYS::getProjPath() . DS . 'log' . DS . $t;
        } else {
            $dir = $logPath . DS . $t;
        }
        Dir::make($dir);
        $str = \date('Y-m-d H:i:s', Config::get('now_time', time())) . $separator  . $separator .  $separator . \implode($separator, array_map('QYS\Log\Log::myJson', $params));
        $logFile = $dir . \DS . $type . '.log';
        co::create(function () use ($logFile,$str)
        {
            $r =  co::writeFile($logFile, $str . "\n",FILE_APPEND);
        });
    }

    /**
     * 实名认证接口
     * @param string  $idcard  身份证号码
     * @param string  $realname  真实姓名
     * @return bool
     */
    public static function idCard($idcard = '', $realname = ''){
        if(empty($idcard) || empty($realname)){
            return false;
        }
        $key = 'bb12e68953d2a9e0d959ac145a52388b';
        $url = "http://op.juhe.cn/idcard/query?key=".$key."&idcard=".$idcard."&realname=".urlencode($realname);
        $res = self::request($url);
        $res = json_decode($res,true);
        if($res['reason'] == '成功' && $res['error_code'] == '0'){
            if($res['result']['res'] == 1){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


}
