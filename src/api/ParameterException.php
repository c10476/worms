<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/5/12
 * Time: 09:26
 */

namespace worms\api;

use worms\core\ApiParams;

class ParameterException extends ApiException
{
    static public function typeInvalid(ApiParams $p)
    {
        return new self("parameter {$p->getKey()} has invalid type. excepted is {$p->getType()}");
    }

    static public function simpleValidateFailed(ApiParams $p)
    {
        return new self("parameter {$p->getKey()} simple validate failed.");
    }

    static public function regValidateFailed(ApiParams $p)
    {
        return new self("parameter {$p->getKey()} reg validate failed.reg expression is \"{$p->getValidateValue()}\"");
    }

    static public function notAllowNull(ApiParams $p)
    {
        return new self("parameter {$p->getKey()} is not allowed to be NULL.[method:{$p->getMethod()}]");
    }
}