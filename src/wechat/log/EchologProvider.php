<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/10
 * Time: 22:03
 */

namespace worms\wechat\log;

use worms\wechat\core\Log;

class EchologProvider implements Log
{
    public function log($msg, $label)
    {
        echo "[$label] " . var_export($msg) . PHP_EOL;
    }

}