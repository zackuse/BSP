<?php
namespace globalunit\utils;
use QYS\Db\Mysql;
use globalunit\utils\Utils;

class DBHelper
{
    /**
     * @param $tblname 数据库名字
     * @param $conds  查询条件
     * @return string sql语句
     */
    static public function querysql($tblname,$conds) {
        if(empty($conds)){
            $sql = "  select * from $tblname";
        }else
        {
            $sql = "  select * from $tblname where $conds ";
        }
        return $sql;
    }

    /**
     * @param $tblname 数据库名字
     * @param $a      数据字典
     * @return string sql语句
     */
    static public function insertsql($tblname,$a) {
        $values=array();
        $columns=array();


        $values=array();
        $columns=array();

        foreach($a as $k=>$v){
            array_push($columns,$k);
            if(gettype($v)=='integer'){
                array_push($values,"$v");
            }elseif (gettype($v)=='string'){
                array_push($values,"'$v'");
            }elseif (gettype($v)=='array'){
                $s =json_encode($v,true);
                array_push($values,"'$s'");
            }elseif (gettype($v)=='null'){
                array_push($values,"''");
            }
            else{
                $clazzname = get_class($v);
                if($clazzname=='DateTime'){
                    $a=$v->format('Y-m-d H:i:s');
                    array_push($values,"'$a'");
                }else{
                    array_push($values,"'$v'");
                }
            }

        }

        $values=join(",",$values);
        $columns=join(",",$columns);

        $sql = " INSERT INTO $tblname ($columns)  VALUES($values) ";
        return $sql;
    }

    static public function array2in($a)
    {
        $b = array();
        foreach($a as $k=>$v){
            array_push($b,"'$v'");
        }
        $c = join(",",$b);
        return $c;
    }

    /**
     * @param $tblname  数据库名字
     * @param $obj     数据字典
     * @param $conds   更新条件
     * @return string  sql语句
     */
    static public function udpatesql($tblname,$obj,$conds) {
        $values = array();
        foreach($obj as $k=>$v) {
            if(gettype($v)=='integer'){
                array_push($values," $k=$v ");
            }elseif (gettype($v)=='string'){
                array_push($values," $k='$v' ");
            }elseif (gettype($v)=='array'){
                $s=json_encode($v);
                array_push($values," $k='$s' ");
            }elseif (gettype($v)=='null'){
                array_push($values," $k='' ");
            }
            else{
                $clazzname = get_class($v);
                if($clazzname=='DateTime'){
                    $a=$v->format('Y-m-d H:i:s');
                    array_push($values," $k='$a' ");
                }else{
                    array_push($values," $k='$v' ");
                }
            }
        }

        $x = join(",",$values);
        $sql = "  UPDATE $tblname set $x where $conds ";
        return $sql;
    }

    static public function query($sql)
    {
        Utils::dump($sql);
        $db = Mysql::getInstance("mysql1");
        $result = $db->query($sql);
        $a=array();
        if($result===false){
            return $a;
        }

        while($row = $result->fetch_assoc())
        {
            array_push($a,$row);
        }
        return $a;
    }

    static public function fetchone($sql)
    {
        Utils::dump($sql);
        $db = Mysql::getInstance("mysql1");
        $result = $db->query($sql);
        $a=array();
        if($result===false){
            return $a;
        }
        return $result->fetch_assoc();
    }

    static public function queryall($tblname,$conds)
    {
        $sql = self::querysql($tblname,$conds);
        Utils::dump($sql);
        $db = Mysql::getInstance("mysql1");
        $result = $db->query($sql);
        $a=array();
        if($result===false){
            return $a;
        }

        while($row = $result->fetch_assoc())
        {
            array_push($a,$row);
        }
        return $a;
    }
    
    static public function query_update($sql)
    {
        Utils::dump($sql);
        $db = Mysql::getInstance("mysql1");
        $result = $db->query($sql);
        $a=array();
        if($result===false){
            return $a;
        }
        return 1;
    }
}
