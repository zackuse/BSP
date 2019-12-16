<?php
use \Yosymfony\Toml\Toml;

use globalunit\utils\Utils;
use QYS\QYS;

$gameroot = dirname(getcwd());
// var_dump($gameroot);

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
$path=joinPaths(QYS::getProjPath(),"..","config","dev.toml");
if (!file_exists($path)) {
    $path=joinPaths(QYS::getProjPath(),"..","config","prod.toml");
}
$myfile = fopen($path, "r");
$content = fread($myfile,filesize($path));

$config = Toml::Parse($content);

return array(
    "pack_path" => array("callback",'lib','scripts',"handler",".."),
    'server_mode' => 'Process',
    'process' => array(
        "worker_num"=>1,
        'callback_class' => 'callback\\SwooleBeanProcess',
    ),
    'LOG_HANDLER'=>0,
    'GAME_HANDLER'=>1,
    "debug"=>array(
        "Test"=>true,
    ),
    'gamename'=>$config["global"]["gamename"],
    'appname'=>$config["global"]["gamename"].'-center',
    'fangda'=>$config["global"]["fangda"],
    'version'=>$config["global"]["version"],
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
