<?php
/**
 * 系统配置服务，默认数据在用代码进行配置
 * 动态修改的数据放在mongodb中，因为mongodb配置无需预先设置表的结构
 */
namespace globalunit\utils;
use QYS\Db\Mongo;
use QYS\Log\Log;

function merge($dest,$src)
{
    if(!isset($dest))
    {
        $src=array();
    }

}

function  array_merge_recursive_distinct(array $array1, array &$array2)
{
    $merged = $array1;
    foreach ($array2 as $key => &$value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }
    return $merged;
}

class Config
{
    static $DEFAULT=array(
        "errmsg"=>array(
            "e10000"=>'系统繁忙',
            "e10001"=>'注册参数错误',
            "e10002"=>'注册验证码错误',
            "e10003"=>'手机号已经注册过',
            "e10004"=>'注册失败，请稍后重试',
            "e10005"=>'当前手机号未注册',
            "e10006"=>'玩家信息不匹配',
            "e10007"=>'密码错误',
            "e10008"=>'禁止登录：您的账户已被禁用！',
            "e10009"=>'绑定失败',
            "e10010"=>'循环绑定',
            "e10011"=>'weixin error',
            "e10012"=>'玩家不存在',
            "e10013"=>'房间已结束',
            "e10014"=>'房间繁忙',
            "e10015"=>'坐下失败',
            "e10016"=>'游戏过程中不能站起',
            "e10017"=>'不能完成准备',
            "e10018"=>'没有坐下不能下注',
            "e10019"=>'没有准备不能下注',
            "e10020"=>'下注超出上限',
            "e10021"=>'抢庄失败',
            "e10022"=>'房卡不足',
            "e10023"=>'创建房间参数错误',
            "e10024"=>'游戏过程中不能准备',
            "e10025"=>'下注超过上限',
            "e10026"=>'当前状态不能下注',
            "e10027"=>'当前状态不能摇骰子',
            "e10028"=>'当前状态不能抢庄',
            "e10029"=>'不能重复坐下',
            "e10030"=>'站起失败',
            "e10031"=>'未获取到unionid',
            "e10032"=>'已经准备',
            "e10090"=>'房卡不足',
            "e10091"=>'该礼包不存在或已被领取',
            "e10092"=>'该俱乐部不存在',
            "e10093"=>'您已在此俱乐部',
            "e10094"=>'您已创建俱乐部',
            "e10095"=>'您未加入该俱乐部，暂不能加入',
            "e10096"=>'当前玩家已操作',
            "e10097"=>'认证姓名错误',
            "e10098"=>'认证身份证错误',
            "e10099"=>'没有认证JWT',
            "e10101"=>'旧密码不能为空',
            "e10102"=>'新密码不能为空',
            "e10103"=>'原密码不匹配',
            "e10104"=>'请输入聊天类型',
            "e10105"=>'请输入聊天内容',
            "e10106"=>'红包金额不能小于零',
            "e10107"=>'请输入红包描述',
            "e10108"=>'红包个数不能小于零',
            "e10109"=>'红包ID不能空',
            "e10110"=>'红包ID不存在',
            "e10111"=>'红包已经领完',
            "e10112"=>'你已经领过红包',
            "e10113"=>'邀请码无效',
            "e10114"=>'内容过长255',
            "e10115"=>'k线时间不匹配',
            "e10116"=>'红包个数不能大于9个',
            "e10117"=>'设置参数信息不符',
            "e10118"=>'金额不足',
            
            //j新增
            "e10119"=>'转入数量必须大于0',
            "e10120"=>'余额不足',
            "e10121"=>'币不足',
            "e10122"=>'币不足,需要转币',
            "e10123"=>'谷歌验证码错误',
            "e10124"=>'转出数量必须大于0',
            "e10125"=>'钱包地址不正确',
            "e10126"=>'只可升级,不可降级',
            "e10127"=>'图形验证码错误',
            "e10128"=>'质押不能超过BSP上限',
            "e10129"=>'时光机可以升舱，不可降舱',
            "e10130"=>'交易密码错误',
            "e10131"=>'重启时光机消耗不足',
            "e10132"=>'实名认证不通过',

            "e10300"=>'购买失败,金额不足',
            "e10301"=>'购买失败,坑位不足',
            "e10302"=>'您不是有效节点，无法操作',
            "e10303"=>'创建钱包失败',
            "e10304"=>'助记词已设置',
            "e10201"=>'参数错误',
            "e10202"=>'EOC不足',
            "e10203"=>'操作失败',
            "e10204"=>'提现金额小于最低额度',
            "e10205"=>'USDT不足',
            "e10206"=>'操作失败，请设置提现地址',
            "e10207"=>'操作失败，请重新操作',
            "e10208"=>'最低参与金额为10',
            "e10209"=>'参数错误，必须是100、200、300、400、500其一',
            "e10210"=>'超出每日可转次数',
            "e10211"=>'无法撤销，已超过撤销期限',
            "e10212"=>'没有找到相关的配置',
            "e10213"=>'提现手续费不足',
        ),

        //英文反馈
        "errmsgen"=>array(
            "e10000"=>'system busy',
            "e10001"=>'wrong registration parameter',
            "e10002"=>'registration verification code error',
            "e10003"=>'mobile number has been registered',
            "e10004"=>'registration failed, please try again later',
            "e10005"=>'the current mobile number is not registered',
            "e10006"=>'player information does not match',
            "e10007"=>'wrong password',
            "e10008"=>'no login: your account has been disabled! ',
            "e10009"=>'binding failed',
            "e1010"=>'loop binding',
            "e10011"=>'weixin error',
            "e10012"=>'player does not exist',
            "e10013"=>'room ended',
            "e10014"=>'the room is busy',
            "e10015"=>'fail to sit down',
            "e10016"=>'cannot stand up during the game',
            "e10017"=>'preparation cannot be completed',
            "e10018"=>'no bet without sitting down',
            "e10019"=>'no bet without preparation',
            "e10020"=>'Bet out of limit',
            "e10021"=>'Failure of Zhuang Zhuang',
            "e10022"=>'insufficient room card',
            "e10023"=>'error in creating room parameters',
            "e10024"=>'cannot prepare during the game',
            "e10025"=>'bet over limit',
            "e10026"=>'current status cannot be wagered',
            "e10027"=>'Cannot roll dice in current state',
            "e10028"=>'You can not rob the villa at present',
            "e10029"=>'cannot sit repeatedly',
            "e1030"=>'failed to stand up',
            "e10031"=>'unionid not obtained',
            "e10032"=>'ready',
            "e10090"=>'insufficient room card',
            "e10091"=>'the gift package does not exist or has been claimed',
            "e10092"=>'the club does not exist',
            "e10093"=>'you are here',
            "e10094"=>'you have created a club',
            "e10095"=>'You are not allowed to join the club',
            "e10096"=>'the current player has operated',
            "e10097"=>'wrong authentication name',
            "e10098"=>'authentication ID card error',
            "e10099"=>'JWT not certified',
            "e110101"=>'old password cannot be empty',
            "e10102"=>'new password cannot be empty',
            "e10103"=>'the original password does not match',
            "e10104"=>'please enter chat type',
            "e10105"=>'please enter chat content',
            "e10106"=>'red packet amount cannot be less than zero',
            "e10107"=>'please enter the red packet description',
            "e10108"=>'the number of red packets cannot be less than zero',
            "e10109"=>'red packet ID cannot be empty',
            "e110110"=>'red packet ID does not exist',
            "e1011"=>'red packet has been collected',
            "e1012"=>'you have received the red envelope',
            "e1013"=>'invalid invitation code',
            "e10114"=>'content too long 255',
            "e10115"=>'K-line time mismatch',
            "e10116"=>'the number of red packets cannot be greater than 9',
            "e10117"=>'setting parameter information does not match',
            "e10118"=>'insufficient amount',
            "e10119"=>'transfer in quantity must be greater than 0',
            "e10120"=>'insufficient balance',
            "e10121"=>'insufficient currency',
            "e10122"=>'Insufficient currency, need to transfer currency',
            "e10123"=>'Google verification code error',
            "e10124"=>'transfer out quantity must be greater than 0',
            "e10125"=>'incorrect wallet address',
            "e10126"=>'Upgrade only, not demote',
            "e10127"=>'graphic verification code error',
            "e10128"=>'pledge cannot exceed the upper limit of BSP',
            "e10129"=>'Time machine can be upgraded, not lowered',
            "e10130"=>'wrong transaction password',
            "e10131"=>'Insufficient consumption of restart time machine',
            "e10132"=>'Real-name authentication does not pass',
            "e10300"=>'purchase failed, insufficient amount',
            "e10301"=>'Failed to purchase, insufficient pit',
            "e10302"=>'you are not a valid node and cannot operate',
            "e10303"=>'failed to create wallet',
            "e10304"=>'mnemonics set',
            "e110201"=>'wrong parameter',
            "e10202"=>'EOC insufficient',
            "e10203"=>'operation failed',
            "e10204"=>'withdrawal amount is less than the minimum amount',
            "e10205"=>'insufficient usdt',
            "e10206"=>'operation failed, please set the withdrawal address',
            "e10207"=>'operation failed, please re operate',
            "e10208"=>'minimum participation amount is 10',
            "e10209"=>'wrong parameter, must be one of 100, 200, 300, 400, 500',
            "e10210"=>'exceeding the daily turnaround times',
            "e10211"=>'Cannot be revoked. The revocation period has expired',
            "e10212"=>'no relevant configuration found',
            "e10213"=>'Insufficient Commission for withdrawal',
        ),

        "tuiguang"=>[
            "appurl"=>"https://fir.im/ar48",
            "tuiguangurl"=>"http://reg.169129.com/register/king.reg.html",
        ],
        // 提现
        "tixian"=>[
            'sxf'=>0.02,
        ],

        //时光机配置
        "machines_price"=>[100,500,1000,],
        //时光机产出(bsp)
        "machines_output"=>[0.1,0.5,1,],
        //时光机穿梭力
        "machines_through"=>[
            "price"=>0.01, //穿梭力价格（bsp）
            "usdt2through"=>10,//10个usdt产生一个穿梭力
            "pledge2through"=>100,//100个BSP质押产生一个穿梭力
            "restartcost"=>0.05,//重启时光机扣除费用
        ],
        //质押产生的穿梭力
        "through"=>[
            "100"=>2,
            "200"=>4,
            "300"=>6,
            "400"=>8,
            "500"=>10,
        ],
        //首次购买时光机产生的穿梭力
        "first_machine_through"=>[
            "100"=>0.01,
            "500"=>0.015,
            "1000"=>0.02,
        ],
        //二次链接（直推购买）时光机产生的穿梭力
        "second_machine_through"=>[
            "100"=>0.1,
            "500"=>0.2,
            "1000"=>0.3,
        ],
        //超级舰长的伞下加成
        "captain"=>[
            "teamadd"=>0.02, //伞下团队整体穿梭力的 2%加成
            "machineadd"=>0.05, //可获得伞下市场购买时光机的 5%收益
            "superadd"=>0.1 , //顶级账号得整体市场时光机置换10%收益。
            "max"=>5000 , //超级舰长限制-穿梭力。
            "machinecount"=>3 , //超级舰长限制-超级时光机。
        ],
        // BSP计划配置
        "BSP"=>[
            "plan"=>"1000万",
            "year1day"=>56000, //第一年 每天释放56000个BSP，大家一起瓜分，通过穿梭力多少平均分配
            "plannum"=>10000000,
            "price" =>0.1, //1个bsp=0.1个usdt
        ],

        // 短信信息.
        "sms_info"=>array(
            // 发送接口.
            'api_url'=>'https://dx.ipyy.net/smsJson.aspx',
            // 企业ID.
            'userid'=>'',
            // 发送用户账号.
            'account'=>'9B000198',
            // 发送接口密码. -- md5加密 md5采用32位大写
            'password'=>'9B00019858',
            // 出发类型.
            'action'=>'send',
        ),
        // OSS信息.
        "oss_info"=>array(
            // key
            'oss_key'=>'LTAIKDwA8FQrt3031',
            // secret
            'oss_secret'=>'15tS1xeZ4K2rt0mWGxsdR3L1ORiBF7p',
            // endpoint
            'oss_endpoint'=>'1oss-cn-beijing.aliyuncs.com',
            // bucket
            'oss_bucket'=>'1xyfmgy',
            // url
            'oss_url'=>'1https://xyfmgy.oss-cn-beijing.aliyuncs.com/',
        ),
        //节点信息
        "WALLET_SERVER_CONFIG"=>array(
            'ip'=>"47.56.103.3111",
            'port'=>"80",
            'merchantid'=>"12345678",
            "merchantkey"=>"dhasdiuahwfiuagbvkasbdiasbkcgafbasdas"
        ),
    );


    static $DBCONFIG=array();

    public static function loadconfig()
    {
        $conn = Mongo::getInstance('mongo1');
        $gamename=$GLOBALS['GAMENAME'];
        $db=$conn->$gamename;
        $collection=$db->gameconfig;

        foreach (self::$DEFAULT as $key => $value) {
            if (isset($collection)) {
                $cursor = $collection->findOne(["_id"=>$key]);
                if(isset($cursor)){
                    $tmp=[];
                    foreach($cursor as $key1=>$value1){
                        if($key1!="_id"){
                            $tmp[$key1]=$value1;
                        }
                    }
                    self::$DBCONFIG[$key]=$tmp;
                }   
            }
        }
    }

    public static function getallconfig()
    {
        global $DEFAULT,$DBCONFIG;
        $a1=self::$DEFAULT;
        $a2=self::$DBCONFIG;
        $a3=array_merge_recursive_distinct($a1,$a2);
        return $a3;
    }

    public static function get()
    {
        $d=self::$DBCONFIG;
        foreach (func_get_args() as $v) {
            $k=$v;
            if(!isset($d[$k])){
                $d=null;
                break;
            }
            $d=$d[$k];
        }

        if(isset($d)){
            return $d;
        }

        $d=self::$DEFAULT;

        foreach (func_get_args() as $v) {
            $k=$v;
            if(!isset($d[$k])){
                $d=null;
                break;
            }
            $d=$d[$k];
        }
        assert(isset($d),"没有找到相关的配置".func_get_args()[0]);
        return $d;
    }

    public static function getdefault()
    {
        return self::$DEFAULT;
    }

    public static function getdbconfig()
    {
        return self::$DBCONFIG;
    }
}




