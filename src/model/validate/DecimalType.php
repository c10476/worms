<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/5/8
 * Time: 10:53
 */

namespace worms\model\validate;

use worms\model\InvalidFieldValue;

class DecimalType extends Type
{
    protected function checkRange()
    {
        $flag = false;

        if ($this->fieldInfo['precision'] && $this->fieldInfo['scale']) {
            $flag = strlen($this->value) > $this->fieldInfo['precision'];
            if (!$flag) {
                list(, $scale) = explode('.', $this->value);
                $flag = strlen($scale) > $this->fieldInfo['scale'];
            }
        }
        if ($flag) {
            $this->customError(InvalidFieldValue::ERR_OUTOFRANGE);
            $msg = '"' . $this->getFieldName() . '"' . ":{$this->value} ";
            $msg .= "精度超出限制 [{$this->fieldInfo['precision']},{$this->fieldInfo['scale']}]";
            throw new InvalidFieldValue($msg, InvalidFieldValue::ERR_OUTOFRANGE);
        }

    }

}