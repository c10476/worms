<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/12
 * Time: 11:22
 */

namespace worms\core;

class  App
{
    /**
     * @desc   start 开始一个项目
     * @author storn
     * @throws AppException
     */
    static public function start()
    {
        set_exception_handler('worms\core\App::handleException');
        register_shutdown_function('worms\core\App::fatalError');
        set_error_handler('worms\core\App::appError');

        define('REQUEST_ID', uniqid());
        define('START_TIME', microtime(true));

        error_reporting(Config::get('error_reportiong', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING));
        ini_set('display_errors', false);

        if (!Config::get('vpath')) {
            throw new AppException("VPATH can't be empty!", 'VPATH_EMPTY');
        }

        define('VPATH', Config::get('vpath'));
        //定义是否AJAX请求
        define('IS_AJAX',
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) and
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        );
        define('IS_CLI', PHP_SAPI == 'cli');
        if (IS_CLI) {
            $url = 'CMD "' . implode(" ", $_SERVER['argv']) . '"';
        } else {
            $url = (isset($_SERVER['HTTPS']) ? "https://" : "http://")
                . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_ADDR'])
                . $_SERVER['REQUEST_URI'];
        }
        define('__URL__', $url);

        self::loadConf();
        defined('DEBUG') || define('DEBUG', Config::get('debug') == true);
        ini_set('date.timezone', Config::get('timezone', 'PRC'));

        if (Config::get('dispatch', true))
            Dispatcher::dispatch();
    }

    /**
     * @desc   loadConf 加载配置文件
     * @author storn
     */
    static private function loadConf()
    {
        //加载默认配置文件
        Config::loadFileConf(dirname(__DIR__) . '/convention.php');
        //加载配置文件
        $confPath       = Config::get('conf_path', VPATH . '/conf');
        $confFilesArray = Config::get('conf_file', ['conf.php']);
        if (is_string($confFilesArray)) {
            $confFilesArray = [$confFilesArray];
        }
        foreach ($confFilesArray as $file)
            Config::loadFileConf($confPath . DIRECTORY_SEPARATOR . $file);
        //根据域名区分各个环境
        foreach (Config::get('host_conf', []) as $reg => $file)
            preg_match($reg, $_SERVER['HTTP_HOST'])
            AND
            Config::loadFileConf($confPath . DIRECTORY_SEPARATOR . $file);
    }

    /**
     * 自定义异常处理
     *
     * @access public
     *
     * @param \Throwable $e 异常对象
     */
    static public function handleException($e)
    {
        if (!in_array(get_class($e), Config::get('no_logged_exception'))) {
            Log::error($e);
        }
        if (!headers_sent() && DEBUG) {
            Response::create()->exception($e);
        }
    }

    // 致命错误捕获
    static public function fatalError()
    {
        if (($e = error_get_last()) && DEBUG) {
            Response::create()->error($e['message'], 'fatal_error', $e);
        }
        $e && Log::error($e);

        self::lastLog();
    }

    /**
     * @desc   lastLog
     * @author storn
     */
    static private function lastLog()
    {
        $info[]   = __URL__;
        $info[] = $_SERVER['SERVER_ADDR'] ??'';
        $info[] = self::getRealUserIp() ?? '';
        $info[] = sprintf(
            "\t%s\t[File loaded: %d ]\t[ time: %.6f s]",
            $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            count(get_included_files()),
            microtime(true) - START_TIME
        );

        Log::write(implode("\t", array_filter($info)), Log::INFO);
        Log::save();
    }

    /**
     * @desc   getUserIp
     * @author storn
     * @return string
     */
    static public function getRealUserIp()
    {
        static $ip;
        if (null === $ip) {
            $ip  = '';
            $arr = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
            foreach ($arr as $k) {
                $_ip = explode(',', $_SERVER[$k], 2)[0];
                if (filter_var($_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ip = $_ip;
                    break;
                }
            }
        }

        return $ip;

    }

    /**
     * 自定义错误处理
     *
     * @access public
     *
     * @param int    $errno   错误类型
     * @param string $errstr  错误信息
     * @param string $errfile 错误文件
     * @param int    $errline 错误行数
     *
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline)
    {
        (Config::get('error_reportiong') & $errno) === $errno &&
        Log::error("appError [{$errno}] $errstr  @{$errfile} +{$errline}");
    }
}