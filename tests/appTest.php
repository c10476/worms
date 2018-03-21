<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/12
 * Time: 21:01
 */

namespace storn\worms\tests;

use worms\core\App;
use worms\core\Config;
use worms\core\Log;

class appTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        Config::set('VPATH', __DIR__);
    }

    public function test()
    {
        App::start();
        $this->assertEquals(VPATH, Config::get('vpath'));
        $this->assertEquals('1234', Config::get('aaa'));
    }

    public function testHostConf()
    {
        $_SERVER['HTTP_HOST'] = 'www.test.com';
        Config::set('host_conf', ['/^.+.test.com$/' => 'host.php']);
        App::start();
        $this->assertEquals('12345', Config::get('aaa'));
    }

    public function testLog()
    {
        Config::set('VPATH', dirname(__DIR__));
        App::start();
        Config::batchSet([
            'log' => [
                'time_format' => ' c ',
                'file_size'   => 2097152,
                'filepath'    => __DIR__ . '/log',
                'apart_level' => [],
                'level'       => ['log', 'error', 'info', 'sql', 'notice', 'alert'],
                'suffix'      => REQUEST_ID . "\t" . __URL__,
            ],
        ]);
        Log::write('1221312312', Log::ALERT);
        Log::write('1221312312444545', Log::ALERT);
        Log::write('1221312312222233123', Log::ALERT);
    }

    public function testDb()
    {
        Config::set('VPATH', dirname(__DIR__));
        Config::set('dispatch', false);
        Config::set('db', [
            'default' => [
                'is_dev_mode' => true,
                'conn'        => [
                    'driver'   => 'pdo_mysql',
                    'dbname'   => 'zbl',
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'password' => '123456',
                ],
            ],
        ]);
        App::start();
    }
}
