<?php
/**
 * Created by PhpStorm.
 * User: xhkj
 * Date: 2018/7/10
 * Time: 下午6:01
 */

namespace globalunit\utils;
use QYS\Db\Redis;
use globalunit\utils\JWTUtil;
use QYS\Core\Config as QYSConfig;
use QYS\Cache\Factory as CFC;
use globalunit\logic\TuiJianLogic;

class Utils
{
    public static $output_dump=true;
    public static $cache = null;
    public static function isvalidphonenumber($mobile)
    {
        if(preg_match("/^1[34578]\d{9}$/", $mobile)) {
            return true;
        }
        return false;
    }

    public static function currentmonth($utc)
    {
        $nowtime = date('Ym',$utc);
        return $nowtime;
    }

    public static function today()
    {
        $nowtime = date('Ymd');
        return $nowtime;
    }

    public static function checkjwt($jwt)
    {
        $jwtobj = JWTUtil::decode($jwt, 'lua-resty-jwt', array('HS256'));
        if(empty($jwtobj) || empty($jwtobj->user_id) || empty($jwtobj->token) ){
            return null;
        }

        return $jwtobj;
    }

    public static function getmonthday($year,$month)
    {
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        return $days;
    }

    public static function getcurrentyear()
    {
        $thismonth=date('Y');
        return intval($thismonth);
    }

    public static function getcurrentmonth()
    {
        $thismonth=date('m');
        return intval($thismonth);
    }

    public static function getcurrentday()
    {
        $thisday=date('d');
        return intval($thisday);
    }

    public static function joinPaths()
    {
        $args = func_get_args();

        $paths = [];

        foreach ($args as $arg) {
            $paths[] = trim($arg, DIRECTORY_SEPARATOR);
        }

        $paths = array_filter($paths);

        return DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $paths);
    }

    public static function dump($a)
    {
        if(self::$output_dump){
            $trace=debug_backtrace();
            $caller0=$trace[0];
            $caller=$trace[1];

            if(isset($caller['class'])){
                $c = $caller['class'];
                $f=$caller['function'];
                $file = $caller0['file'];

                $l=$caller0['line'];
                print_r("dump from: class $c ($file): in function '$f' line $l".PHP_EOL);
                var_dump($a);
            }else{
//                var_dump($caller0);
//                var_dump($caller);
                $f=$caller['function'];
                $l=$caller['line'];
                $file = $caller0['file'];
                $l=$caller0['line'];
                print_r("dump from ($file): in function '$f' in line $l".PHP_EOL);
                var_dump($a);
            }


//            var_dump($a);
        }else{

        }
    }

    public static  function imagecropper($source_path,$workerid=0, $target_width = 142, $target_height = 142)
    {
        $source_info = getimagesize($source_path);
        $source_width = $source_info[0];
        $source_height = $source_info[1];
        $source_mime = $source_info['mime'];
        $source_ratio = $source_height / $source_width;
        $target_ratio = $target_height / $target_width;

        // 源图过高
        if ($source_ratio > $target_ratio)
        {
            $cropped_width = $source_width;
            $cropped_height = $source_width * $target_ratio;
            $source_x = 0;
            $source_y = ($source_height - $cropped_height) / 2;
        }
        // 源图过宽
        elseif ($source_ratio < $target_ratio)
        {
            $cropped_width = $source_height / $target_ratio;
            $cropped_height = $source_height;
            $source_x = ($source_width - $cropped_width) / 2;
            $source_y = 0;
        }
        // 源图适中
        else
        {
            $cropped_width = $source_width;
            $cropped_height = $source_height;
            $source_x = 0;
            $source_y = 0;
        }

        switch ($source_mime)
        {
            case 'image/gif':
                $source_image = imagecreatefromgif($source_path);
                break;

            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;

            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;

            default:
                return false;
                break;

        }

        $target_image = imagecreatetruecolor($target_width, $target_height);
        $cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);

        // 裁剪
        imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
        // 缩放
        imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);


        $randNumber = $workerid.mt_rand(00000, 99999). mt_rand(000, 999);
        $fileName = substr(md5($randNumber), 8, 16) .".jpg";
        imagepng($target_image,$source_path);
        imagedestroy($target_image);

    }

    public static function getCache()
    {
        if(empty(self::$cache)){
            self::$cache=CFC::getInstance("Redis","redis2");
            self::$cache->selectDb(1);
        }

        return self::$cache;
    }

    public static function coget($domain,$path,$port=80,$ssl=false,$timeout=2)
    {
        $cli = new \Swoole\Coroutine\Http\Client($domain, $port,$ssl);
        $cli->setHeaders([
            'Host' => $domain,
            "User-Agent" => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml', 'Accept-Encoding' => 'gzip',]);
        $cli->set(['timeout' => $timeout]);

        $cli->get($path);
        $cli->close();
        return array("statusCode"=>$cli->statusCode,"body"=>$cli->body);
    }

    public static function getDateMes($time, $releaseTime){
        if($time - $releaseTime <= 60){
            $data = ['type'=>'zuixin','text'=>'刚刚'];
        }else if($time - $releaseTime <= 3600){
            $unicode_time = ceil(($time - $releaseTime) / 60);
            $data = ['type'=>'zuixin','text'=>$unicode_time.'分钟前'];
        }else if($time - $releaseTime <= 3600 * 24){
            $unicode_time = ceil(($time - $releaseTime) / 3600);
            $data = ['type'=>'zuixin','text'=>$unicode_time.'小时前'];
        }else{
            $unicode_time = ceil(($time - $releaseTime) / 3600 / 24);
            $data = ['type'=>'zuixin','text'=>$unicode_time.'天前'];
        }
        return $data;
    }


    //大区一条线满足固定人数，剩下线的总和满足固定人数,太阳线
    public static function getlevel($rediscli,$uid){
        $daqucount=0;
        $othertotal=0;
        //获得直推列表
        $maplvl1 = array();
        $levle1list = TuiJianLogic::xiajilistlevel($rediscli,$uid,1);
        foreach ($levle1list as $key => $value) {
            $uidsan = $value["uid"];
            //TODO 测试数据
            // $validcount = TuiJianLogic::xiajicount($rediscli,$uidsan);
            $validcount = TuiJianLogic::getvalidsanxia($rediscli,$uidsan);
            array_push($maplvl1, ["uid"=>$uidsan,"validcount"=>$validcount]);
        }
        usort($maplvl1, function($a,$b){
            return $b["validcount"]-$a["validcount"];
        });
        // Log::var_dump($maplvl1);
        if (count($maplvl1)>0) {
            $daqucount = $maplvl1[0]["validcount"];
            for ($i=1; $i < count($maplvl1); $i++) { 
                $othertotal = $othertotal + $maplvl1[$i]["validcount"];
            }
        }

        $level = [];
        for ($f = 0; $f < Config::get('xingjijiang', 'xingji'); $f++) {
            $ji = $f+1;
            $countlimit = Config::get('xingjijiang', "xing$ji"."rs");
            if ($daqucount >= $countlimit && $othertotal >= $countlimit) {
                $level['rs'] = Config::get('xingjijiang', "xing$ji"."rs");
                $level['bili'] = Config::get('xingjijiang', "xing$ji"."bili");
                $level['lv'] = $ji;
            }
        }

        return $level;
    }


    //合伙人判断-伞下任意两条线有四星就是合伙人
    public static function checkhehuoren($rediscli,$uid){
        $validzhitui = TuiJianLogic::getvalidzhitui($rediscli, $uid);
        if ($validzhitui < Config::get('xingjijiang', 'shangxing')) {
            return false;
        }
        
        $lv1list = TuiJianLogic::xiajilistlevel($rediscli, $uid, 1);

        $line = 0;
        foreach ($lv1list as $lk=>$lv){
            $find = 0;
            //首先找直推是否是四星
            $lvzhitui = Utils::getlevel($rediscli,$lv['uid']);
            if (count($lvzhitui)>0 && $lvzhitui['lv']==4) {
                $line++;
                continue;
            }
            //直推没有，找所有下级
            $sanxialist = TuiJianLogic::getvalidsanxiayeji($rediscli, $lv['uid']);
            foreach ($sanxialist as $lk=>$lvsan){
                //下级是否有四星
                $lvsan = Utils::getlevel($rediscli,$lvsan['uid']);
                if (count($lvsan)>0 && $lvsan['lv']==4) {
                    $line++;
                    $find = 1;
                    break;
                }
            }
            if ($find==1) {
                continue;
            }
        }

        if ($line >= Config::get('xingjijiang', 'hehuoren'.'sl')) {
            return true;
        }

        return false;
    }

}

$config = QYSConfig::get('dump_enable');
if (isset($config) && $config==true) {
    Utils::$output_dump=true;
}else{
    Utils::$output_dump=false;
}

