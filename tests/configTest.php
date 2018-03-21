<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/12
 * Time: 20:38
 */

namespace storn\MMPHP\tests;


use worms\core\Config;

class configTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        Config::batchSet(
            [
                'test' => 123,
                'key'  => [123, 456],
            ]
        );
        $this->assertEquals(Config::get('TEST'), '123');
        $this->assertEquals(Config::get('key'), [123, 456]);
    }

    public function test2()
    {
        Config::set('test', 12);
        $this->assertEquals(12, Config::get('test'));
        Config::set('test', 34);
        $this->assertEquals(34, Config::get('test'));
    }
}
