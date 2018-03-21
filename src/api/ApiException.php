<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/13
 * Time: 14:15
 */

namespace worms\api;

use worms\core\AppException;

class ApiException extends AppException
{
    public function __construct($msg, $errno = 'BUSINESS_ERROR', array $detail = [])
    {
        parent::__construct($msg, $errno, $detail);
    }
}