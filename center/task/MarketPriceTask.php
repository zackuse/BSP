<?php

use QYS\Db\Redis;
use QYS\Protocol\Response;
use QYS\Protocol\Request;
use QYS\Db\Mysql;
use QYS\Log\Log;
use globalunit\utils\DBHelper;
use globalunit\utils\config;
use globalunit\utils\RequestHelper;
use globalunit\utils\Utils;
use globalunit\utils\KeysUtil;
use QYS\Core\Config as QYSConfig;
use globalunit\utils\MyLocker;
use globalunit\logic\UserLogic;
use globalunit\logic\RedisLogic;
use QYS\third\Crypto\XXTEA;
use globalunit\utils\GenID;
use Carbon\Carbon;
use logic\MarketLogic;

$rediscli=Redis::getInstance("redis1");
var_dump("Here you are :".__FILE__);

$mysql      = Mysql::getInstance("mysql1");

$symbol_pairs = [
	'BTC_USDT','ETH_USDT',
];

//币安交易所
for ($i=0; $i < count($symbol_pairs); $i++) { 
    $url = "https://data.block.cc/api/v1/ticker?market=binance&symbol_pair=".$symbol_pairs[$i];
    $source = file_get_contents($url);
    $returnData = json_decode($source,true);

    if ($returnData["code"]==0) {
	    $tickerdata = $returnData["data"];
	    MarketLogic::setprice($rediscli,$tickerdata,"binance");
    }
}

//okex交易所
for ($i=0; $i < count($symbol_pairs); $i++) { 
    $url = "https://data.block.cc/api/v1/ticker?market=okex&symbol_pair=".$symbol_pairs[$i];
    $source = file_get_contents($url);
    $returnData = json_decode($source,true);

    if ($returnData["code"]==0) {
	    $tickerdata = $returnData["data"];
	    MarketLogic::setprice($rediscli,$tickerdata,"okex");
    }
}

//火币交易所
for ($i=0; $i < count($symbol_pairs); $i++) { 
    $url = "https://data.block.cc/api/v1/ticker?market=huobipro&symbol_pair=".$symbol_pairs[$i];
    $source = file_get_contents($url);
    $returnData = json_decode($source,true);

    if ($returnData["code"]==0) {
	    $tickerdata = $returnData["data"];
	    MarketLogic::setprice($rediscli,$tickerdata,"huobipro");
    }
}

//存放行情数据
$url = "https://data.block.cc/api/v1/exchange_rate";
$source = file_get_contents($url);
$returnData = json_decode($source,true);
$pricecny = 0;
if ($returnData["code"]==0) {
    $tickerdata = $returnData["data"];
    $pricecny = $tickerdata['rates']["CNY"];
}
Log::var_dump($pricecny);

$url = "https://data.block.cc/api/v1/price?symbol=btc,usdt,eth";
$source = file_get_contents($url);
$returnData = json_decode($source,true);
if ($returnData["code"]==0) {
    $tickerdata = $returnData["data"];
    MarketLogic::setrate($rediscli,$tickerdata,$pricecny);
}
