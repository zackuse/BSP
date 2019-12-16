<?php
/**
 * Created by PhpStorm.
 * User: xhkj
 * Date: 2018/7/9
 * Time: 上午9:50
 */
namespace validator\adapter;
use validator\IValidator;

class ac implements IValidator
{
    private $col = null;
    private $config = null;
    public function __construct($params)
    {
        $this->col = $params['col'];
        $this->config = $params['config'];
    }

    /**
     * @param $obj 需要过滤的数据字典
     * @return mixed
     */
    public function validate($obj,$op='insert')
    {
        if($op=='select'){
            $obj[$this->col] =intval($obj[$this->col]);
        }else{
            unset($obj[$this->col]);
        }
        return $obj;
    }
}
