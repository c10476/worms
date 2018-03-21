<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/5/8
 * Time: 10:52
 */

namespace worms\model\validate;

use worms\model\InvalidFieldValue;

class DatetimeType extends Type
{
    protected function checkNull()
    {
        if ($this->fieldInfo['nullable'] === false && is_null($this->value)) {
            $this->value = new \DateTime();
            $this->rfField->setValue($this->object, $this->value);
        }
    }

    function checkRange()
    {
        if (!($this->value instanceof \DateTime)) {
            throw new InvalidFieldValue("{$this->getFieldName()} 请输入正确的时间格式", 'FIELD_NOT_DATETIME');
        }
    }

}