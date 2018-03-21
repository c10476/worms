<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace worms\core;

use worms\log\File;

/**
 * Class Log
 *
 * @package think
 *
 * @method void log($msg) static
 * @method void error($msg) static
 * @method void info($msg) static
 * @method void sql($msg) static
 * @method void notice($msg) static
 * @method void alert($msg) static
 */
class Log
{
    const LOG = 'log';
    const ERROR = 'error';
    const INFO = 'info';
    const SQL = 'sql';
    const NOTICE = 'notice';
    const ALERT = 'alert';

    // 日志信息
    protected static $log = [];
    // 配置参数
    protected static $config = [];
    // 日志类型
    protected static $type = ['log', 'error', 'info', 'sql', 'notice', 'alert'];
    // 日志写入驱动
    /**
     * @var File
     */
    protected static $driver;

    // 当前日志授权key
    protected static $key;

    /**
     * 日志初始化
     *
     * @param array $config
     */
    public static function init($config = [])
    {
        self::$config = $config;
        self::$driver = new File($config);
    }

    /**
     * 获取日志信息
     *
     * @param string $type 信息类型
     *
     * @return array
     */
    public static function getLog($type = '')
    {
        return $type ? self::$log[$type] : self::$log;
    }

    /**
     * 记录调试信息 延迟写入
     *
     * @param mixed  $msg  调试信息
     * @param string $type 信息类型
     *
     * @return void
     */
    public static function record($msg, $type = 'log')
    {
        if (is_null(self::$driver)) {
            self::init(Config::get('log'));
        }
        //debug
        if (self::$config['status'] === false) {
            return;
        }
        if (empty(self::$log[$type])) {
            self::$log[$type][] = ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>";
        }
        self::$log[$type][] = $msg;
    }

    /**
     * 清空日志信息
     *
     * @return void
     */
    public static function clear()
    {
        self::$log = [];
    }

    /**
     * 保存调试信息
     */
    public static function save()
    {
        if (!empty(self::$log)) {
            if (is_null(self::$driver)) {
                self::init(Config::get('log'));
            }

            if (empty(self::$config['level'])) {
                // 获取全部日志
                $log = self::$log;
            } else {
                // 记录允许级别
                $log = [];
                foreach (self::$config['level'] as $level) {
                    if (isset(self::$log[$level])) {
                        $log[$level] = self::$log[$level];
                    }
                }
            }
            self::$driver->save($log);
        }

    }

    /**
     * 实时写入日志信息 并支持行为
     *
     * @param mixed  $msg   调试信息
     * @param string $type  信息类型
     * @param bool   $force 是否强制写入
     *
     * @return bool
     */
    public static function write($msg, $type = 'log', $force = false)
    {
        // 封装日志信息
        if (true === $force || empty(self::$config['level'])) {
            $log[$type][] = $msg;
        } elseif (in_array($type, self::$config['level'])) {
            $log[$type][] = $msg;
        } else {
            return false;
        }

        if (is_null(self::$driver)) {
            self::init(Config::get('log'));
        }

        // 写入日志
        return self::$driver->save($log);
    }

    /**
     * 静态调用
     *
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::$type)) {
            array_push($args, $method);

            return call_user_func_array('\\worms\\core\\Log::record', $args);
        }
    }

}
