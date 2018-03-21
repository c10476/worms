<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/12
 * Time: 13:49
 */

namespace worms\core;

class Config
{
    static private $__config = [];

    /**
     * @desc   set 设置配置
     * @author storn
     *
     * @param string $key   key
     * @param mixed  $value 值
     */
    static public function set($key, $value)
    {
        $key = strtoupper($key);
        if (is_array($value) && isset(self::$__config[$key]) && is_array(self::$__config[$key])) {
            self::$__config[$key] = array_merge(self::$__config[$key], $value);
        } else {
            self::$__config[$key] = $value;
        }
    }

    /**
     * @desc   batchSet 批量设置
     * @author storn
     *
     * @param array $config 配置数组
     */
    static public function batchSet(array $config)
    {
        foreach ($config as $k => $v) {
            self::set($k, $v);
        }
    }

    /**
     * @desc   loadFileConf 加载文件配置
     * @author storn
     *
     * @param string $confFile 配置文件路径
     *
     * @throws \InvalidArgumentException
     */
    static public function loadFileConf(string $confFile)
    {
        if (file_exists($confFile)) {
            $conf = include $confFile;
            if (is_array($conf)) {
                self::batchSet($conf);
            } else {
                throw new \InvalidArgumentException("配置无效 @" . $confFile);
            }
        }
    }

    /**
     * @desc   get
     * @author storn
     *
     * @param string     $key     key
     * @param null|mixed $default 默认值
     *
     * @return null
     */
    static public function get($key = '', $default = null)
    {
        if ($key == '') {
            return self::$__config;
        }
        if (!strpos($key, '.')) {
            $key = strtoupper($key);

            return isset(self::$__config[$key]) ? self::$__config[$key] : $default;
        }
        // 二维数组设置和获取支持
        list($pKey, $cKey) = explode('.', $key, 2);
        $pKey = strtoupper($pKey);

        return isset(self::$__config[$pKey][$cKey]) ? self::$__config[$pKey][$cKey] : $default;
    }
}