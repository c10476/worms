<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/10
 * Time: 21:45
 */

namespace worms\wechat\cache;

use worms\wechat\core\Cache;

class CacheProvider implements Cache
{
    public function set($key, $value, $expire)
    {
        return \worms\core\Cache::set($key, $value, $expire);
    }

    public function get($key)
    {
        return \worms\core\Cache::get($key);
    }

    public function del($key)
    {
        return \worms\core\Cache::rm($key);
    }

}