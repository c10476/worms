<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/3/31
 * Time: 18:20
 */

namespace storn\worms\tests;

use worms\cache\driver\Memcached;
use worms\cache\driver\Redis;
use PHPUnit\Framework\TestCase;

class testCache extends TestCase
{
    public function testRedis()
    {
        $redis = new Redis([
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'password'   => '',
            'select'     => 0,
            'timeout'    => 0,
            'expire'     => 0,
            'persistent' => false,
            'prefix'     => '',
        ]);

        $redis->set('123', 123, 1);
        $this->assertEquals(123, $redis->get(123));
        $this->assertEquals(false, $redis->add(123, 123, 1));

        sleep(1);
        $this->assertEquals(false, $redis->has(123));
    }

    public function test2()
    {
        $redis = new Redis([
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'password'   => '',
            'select'     => 0,
            'timeout'    => 0,
            'expire'     => 0,
            'persistent' => false,
            'prefix'     => '',
        ]);
        $redis->set('test123', [123]);
        $this->assertEquals([123], $redis->get('test123'));
    }

    public function testMemcached()
    {
        $memcached = new Memcached([
            'host'     => '127.0.0.1',
            'port'     => 11211,
            'expire'   => 0,
            'timeout'  => 0, // 超时时间（单位：毫秒）
            'prefix'   => '',
            'username' => '', //账号
            'password' => '', //密码
            'option'   => [],
        ]);
        echo $memcached->has(123);

    }
}