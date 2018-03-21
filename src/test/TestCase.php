<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/6/19
 * Time: 18:38
 */

namespace worms\test;

use worms\core\App;
use worms\core\Config;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        if (!defined('TEST_VPATH')) {
            define('TEST_VPATH', dirname(__DIR__));
        }
        Config::set('vpath', TEST_VPATH);
        Config::set('conf_path', TEST_VPATH . DIRECTORY_SEPARATOR . 'tests');
        App::start();
    }
}