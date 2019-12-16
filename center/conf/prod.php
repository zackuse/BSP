<?php
use \Yosymfony\Toml\Toml;
use QYS\QYS;

$locations=[
    "login.php",
    "hall.php",
    "wallet.php",
    "machine.php",
    "admin.php",
];

$router =[];
foreach($locations as $r){
    $a=include($r);
    $router = array_merge($router,$a);
}

$dir=dirname(__DIR__).DIRECTORY_SEPARATOR.'static';
$port=10000;


function joinPaths()
{
    $args = func_get_args();

    $paths = [];

    foreach ($args as $arg) {
        $paths[] = trim($arg, DIRECTORY_SEPARATOR);
    }

    $paths = array_filter($paths);

    return DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $paths);
}

$path=joinPaths(QYS::getProjPath(),"..","config","prod.toml");
$myfile = fopen($path, "r");
$content = fread($myfile,filesize($path));


$config = Toml::Parse($content);

return array(
    "pack_path" => array("callback",'lib','scripts',"handler",".."),
    'server_mode' => 'Socket',
    'debug_mode' => 1,
    'socket' => array(
        'host' => '0.0.0.0',               //socket 监听ip
        'port' => $port,                  //socket 监听端口
        'server_type' => 'HTTP',         //socket 业务模型 tcp/udp/http/websocket
        'protocol' => 'Http',           //socket通信数据协议
        'callback_class' => 'callback\\SwooleHttp', //socket 回调类
        'work_mode' => 3,                             //工作模式：1：单进程单线程 2：多线程 3： 多进程
        'worker_num' => 4,                                 //工作进程数
        'max_request' => 10000,                            //单个进程最大处理请求数
        'debug_mode' => 1,                             //打开调试模式
        "daemonize"=>1,                 //是否在后台运行
        'task_worker_num' =>1,
        'dispatch_mode'=>1,
        'document_root' => $dir,
        'enable_static_handler' => true,
    ),
    'server' =>$router,
    'document_root'=>$dir,
    'LOG_HANDLER'=>0,
    'GAME_HANDLER'=>0,
    'dump_enable'=>true,
    'gamename'=>$config["global"]["gamename"],
    'appname'=>$config["global"]["gamename"].'-center',
    'fangda'=>$config["global"]["fangda"],
    'version'=>$config["global"]["version"],
    "debug"=>1,
    'mysql'=>array(
        "mysql1"=>array(
            'host'=>$config["mysql"]["ip"],
            'user'=>$config["mysql"]["user"],
            'password'=>$config["mysql"]["password"],
            'database'=>$config["mysql"]["db"],
            'port' => $config["mysql"]["port"],
        ),
    ),
    'beanstalkd'=>array(
        "beanstalkd1"=>array(
            'host'=>$config["beanstalkd"]["ip"],
            'port' => $config["beanstalkd"]["port"],
        ),
    ),
    'mongo'=>array(
        "mongo1"=>array(
            'host'=>$config["mongo"]["ip"],
            'port' => $config["mongo"]["port"],
        ),
    ),
    'redis'=>array(
        "redis1"=>array(
            'host'=>$config["redis"]["ip"],
            'port' => $config["redis"]["port"],
            'hearbeat'=>true,
        ),
        "cache"=>array(
            'host'=>$config["cache"]["ip"],
            'port' => $config["cache"]["port"],
            'hearbeat'=>true,
        ),
        "queue"=>array(
            'host'=>$config["queue"]["ip"],
            'port' => $config["queue"]["port"],
            'hearbeat'=>true,
        ),
        "task"=>array(
            'host'=>$config["task"]["ip"],
            'port' => $config["task"]["port"],
            'hearbeat'=>true,
        ),
    ),
);
