<?php
/**
 * Created by PhpStorm.
 * User: xhkj
 * Date: 2018/7/9
 * Time: 上午9:47
 */
namespace validator;
use QYS\Core\Factory as CFactory;

class Factory
{
    private static $_map = [
        'ac' => 1,
        'tp' => 1,
    ];

    /**
     * @param $adapter 校验器的名字
     * @param $params  校验参数
     * @return mixed   校验对象实例
     * @throws \Exception
     */
    public static function build($adapter,$params)
    {
        if (isset(self::$_map[$adapter])) {
            $className = __NAMESPACE__ . "\\adapter\\{$adapter}";
        } else {
            $className = $adapter;
        }
        return CFactory::build($className,$params);
    }
}