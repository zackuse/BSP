<?php
/**
 * Created by PhpStorm.
 * User: xhkj
 * Date: 2018/7/9
 * Time: 下午2:31
 */
namespace validator\adapter;
use validator\IValidator;
use utils\Utils;

class tp implements IValidator
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
    public function validate($obj)
    {
        switch ($this->config){
            case "DateTime":
                if(gettype($obj[$this->col])=='string'){
                    $obj[$this->col] = new \DateTime($obj[$this->col]);
                }
                break;
            case "json":
                if(gettype($obj[$this->col])=='string'){
                    $obj[$this->col] = json_decode($obj[$this->col],true);
                }
                break;
        }
        return $obj;
    }
}
