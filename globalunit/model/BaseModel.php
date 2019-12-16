<?php

namespace model;

use QYS\Db\Mysql;
use QYS\Util\Debug;
use utils\DBHelper;
use utils\Utils;
use validator\Factory as fc;

Class BaseModel
{
    // 表的名字
    protected $_tblname = null;
    // 数据库
    protected $_db = null;
    //是否有效的模型
    protected $_isvalid = false;

    protected $_original = null;

    /**对象是否为从数据库中取出来的
     * @return bool
     */
    public function isvalid()
    {
        return $this->_isvalid;
    }

    public function __construct()
    {
        $this->_db = Mysql::getInstance("mysql1");
    }

    /**插入对象
     * @return mixed
     * @throws \Exception
     */
    public function insert()
    {
        $obj = $this->itermember();

        if (!empty($this->_validator)) {
            foreach ($this->_validator as $col => $value) {
                foreach ($value as $name => $val) {
                    $v = fc::build($name, array('col' => $col, "config" => $val));
                    $obj = $v->validate($obj);
                }
            }
        }

        $sql = DBHelper::insertsql($this->_tblname, $obj);
        var_dump($sql);
        $ret= $this->_db->query($sql);

        if($ret){
            $ac=array();
            foreach ($this->_validator as $col => $value) {
                foreach ($value as $name => $val) {
                    if($name=='ac'){
                        $ac[$col]=1;
                    }
                }
            }
//            如果当前数据有自增的主键
            if(array_count_values($ac)>0){
                $id=$this->_db->insert_id;
                $keys=array_keys($ac);
                $key=$keys[0];
                $this->$key=intval($id);
            }
        }
        return $ret;
    }

    /**获得对象实例
     * @param $conds
     * @return bool
     * @throws \Exception
     */
    public function get($conds)
    {
        $member = $this->itermember();
        $sql = DBHelper::querysql($this->_tblname, $conds);
        Utils::dump($sql);

        $result = $this->_db->query($sql);
        if(!$result){
            return;
        }

        $data = $result->fetch_assoc();

        if(!empty($data)){
            $this->_isvalid = true;
            if (!empty($this->_validator)) {
                foreach ($this->_validator as $col => $value) {
                    foreach ($value as $name => $val) {
                        $v = fc::build($name, array('col' => $col, "config" => $val));
                        $data = $v->validate($data,'select');
                    }
                }
            }

            foreach ($member as $k => $val){
                if(isset($data[$k])){
                    $tp = gettype($this->$k);
                    switch($tp){
                    case "integer":
                        $this->$k = IntVal($data[$k]);
                        break;
                    default:
                        $this->$k=$data[$k];
                        break;
                    }
                }
            }
            return true;
        }

        return false;
    }

    public function initWithData($data)
    {
        $member = $this->itermember();

        if(!empty($data)){
            $this->_isvalid = true;
            if (!empty($this->_validator)) {
                foreach ($this->_validator as $col => $value) {
                    foreach ($value as $name => $val) {
                        $v = fc::build($name, array('col' => $col, "config" => $val));
                        $data = $v->validate($data,'select');
                    }
                }
            }

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
            return true;
        }

        return false;
    }

    /**同步对象到数据库
     * @param null $conds  更新条件
     * @return mixed
     */
    public function update($conds=null)
    {
        $member = $this->itermember();
        $sql = DBHelper::udpatesql($this->_tblname,$member,$conds);
        Utils::dump($sql);
        $this->_db->query($sql);
        return $this->_db->affected_rows;
    }

    /**删除对象
     * @param $conds
     * @return mixed
     */
    public function delete($conds)
    {
        $sql = "DELETE FROM `$this->_tblname` WHERE $conds";
        Utils::dump($sql);
        return $this->_db->query($sql);
    }

    /**模型对象转换成哈希表
     * @return array
     */
    public function toarray()
    {
        $a= $this->itermember();
        return $a;
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
}


