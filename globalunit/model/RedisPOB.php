<?php
namespace globalunit\model;
use utils\Utils;


//php对象映射到内存中
class RedisPOB
{
    //对象对应的key
    protected $_key = null;
    //redis数据库的实例
    protected $_r = null;
    protected $_autosave= false;

    public function __construct($key,$r,$auto=false)
    {
        $this->_key=$key;
        $this->_r= $r;
        $this->_autosave = $auto;

        $this->load();
    }

    public function __destruct()
    {
        if($this->_autosave){
            $this->save();
        }
    }

    public function load()
    {   
        $data = $this->_r->hgetall($this->getkey());
        if(isset($data)){
            $tp = gettype($data);
            $member = $this->itermember();
            foreach ($member as $k => $val){
                if(isset($data[$k])){
                    $tp = gettype($this->$k);
                    switch($tp){
                    case "integer":
                        $this->$k = IntVal($data[$k]);
                        break;
                    case "double":
                        $this->$k = DoubleVal($data[$k]);
                        break;
                    default:
                        $this->$k=$data[$k];
                        break;
                    }
                }
            }

        }
    }



    public function getkey()
    {
        return $this->_key;
    }

    public function delete()
    {
        $this->_r->del($this->getkey());
    }

    public function save($except = array())
    {
        $itermember = $this->itermember();

        foreach ($except as $key => $value) {
            if(!empty($key)){
                unset($itermember[$key]);
            }
        }

        $this->_r->hmset($this->_key,$itermember);
    }

    protected function itermember()
    {
        $a = array();
        foreach ($this as $key => $value) {
            if (substr($key, 0, 1) != "_") {
                $a[$key] = $value;
            }
        }
        return $a;
    }

    public function toparam()
    {
        $a= $this->itermember();
        return $a;
    }

}
