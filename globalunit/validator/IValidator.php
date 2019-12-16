<?php
/**
 * Created by PhpStorm.
 * User: xhkj
 * Date: 2018/7/9
 * Time: 上午9:46
 */
namespace validator;

/**
 * Interface IValidator
 * @package validator
 */
interface IValidator
{
    function validate($obj);
}