<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/5/8
 * Time: 10:07
 */

namespace worms\model;

use worms\core\AppException;

class InvalidFieldValue extends AppException
{
    const ERR_NULL = 'FIELD_NOT_ALLOW_NULL';
    const ERR_OUTOFRANGE = 'FIELD_OUT_OF_RANGE';

    static public function fieldNotAllowNull($field)
    {
        return new self("{$field}不允许为空值.", self::ERR_NULL);
    }

    static public function fieldOutOfRange($field, $value)
    {
        return new self("{$field} 超出范围.", self::ERR_OUTOFRANGE, $value);
    }
}