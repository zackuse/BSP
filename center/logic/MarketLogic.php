<?php
namespace logic;

use globalunit\utils\Config;
use globalunit\utils\KeysUtil;
use globalunit\utils\GenID;
use QYS\Util\Debug;
use QYS\Core\Config as CoreConfig;
use QYS\Log\Log;
use model\InvestModel;
use globalunit\logic\TuiJianLogic;
use globalunit\logic\RedisLogic;

class MarketLogic{

    public static function getmarketkey($market,$symbol_pair) {
        $key="bspkuangji:marketprice:".$market.":".$symbol_pair;
        return $key;
    }

    public static function setprice($rediscli,$tickerdata,$market){
        $symbol_pair = $tickerdata['symbol_pair'];
        $timestamps = $tickerdata['timestamps'];
        $last = $tickerdata['last'];

        $key = self::getmarketkey($market,$symbol_pair);
        Log::var_dump($symbol_pair);
        Log::var_dump($key);
        $rediscli->hset($key,"timestamps",$timestamps);
        $rediscli->hset($key,"price",$last);
    }

    public static function setrate($rediscli,$data,$cnyprice){
        $key = "bspkuangji:marketrate:data";
        $keycny = "bspkuangji:marketrate:cnyprice";
        $rediscli->set($key,json_encode($data));
        $rediscli->set($keycny,$cnyprice);
    }

    public static function loadprice($rediscli){
        $keymarketba = "bspkuangji:marketprice:binance:";
        $keymarketok = "bspkuangji:marketprice:okex:";
        $keymarkethb = "bspkuangji:marketprice:huobipro:";

        $priceba = $rediscli->hget($keymarketba."ETH_USDT","price");
        $priceok = $rediscli->hget($keymarketok."ETH_USDT","price");
        $pricehb = $rediscli->hget($keymarkethb."ETH_USDT","price");
        $price_eth = round((($priceba+$priceok+$pricehb)/3),4);

        $priceba = $rediscli->hget($keymarketba."BTC_USDT","price");
        $priceok = $rediscli->hget($keymarketok."BTC_USDT","price");
        $pricehb = $rediscli->hget($keymarkethb."BTC_USDT","price");
        $price_btc = round((($priceba+$priceok+$pricehb)/3),4);

        return array("eth"=>$price_eth,"btc"=>$price_btc,"usdt_eth"=>round(1/$price_eth,4),"usdt_btc"=>round(1/$price_btc,4));
    }

    public static function loadrate($rediscli){
        $key = "bspkuangji:marketrate:data";
        $keycny = "bspkuangji:marketrate:cnyprice";
        $data = json_decode($rediscli->get($key));
        $cnyprice = $rediscli->get($keycny);
        $ratelist = array();
        foreach ($data as $key => $value) {
            array_push($ratelist, ['symbol'=>$value->symbol,"change_daily"=>$value->change_daily,'cnyprice'=>$value->price*$cnyprice]);
        }

        return $ratelist;
    }
}
