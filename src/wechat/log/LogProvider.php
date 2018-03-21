<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/10
 * Time: 21:48
 */

namespace worms\wechat\log;


use worms\wechat\core\Log;

class LogProvider implements Log
{
    public function log($msg, $label)
    {
        \worms\core\Log::write("[{$label}]\t" . var_export($msg, true));
    }

}