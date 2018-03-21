<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/14
 * Time: 10:36
 */

namespace worms\core;

class Dispatcher
{
    /**
     * @desc   dispatch 调度
     * @author storn
     */
    static public function dispatch()
    {
        defined('APP_PATH') || define('APP_PATH', Config::get('app_path'));
        $layer  = Config::get('dispatcher.layer', 's');
        $action = isset($_GET[$layer]) ? $_GET[$layer] : Config::get('dispatcher.default_action', '');

        $class = str_replace('/', '\\', $action);
        if ($namespace = Config::get('dispatcher.namespace', '')) {
            $class = $namespace . "\\" . $class;
        }
        if (!class_exists($class)) {
            $class = preg_replace('/\\\\[a-zA-Z0-9]+$/', '\\' . Config::get('dispatcher.default_index', 'index'), $class);
            if (!class_exists($class)) {
                throw new AppException("path {$action} missing ", 'module_not_fund', $action);
            }
        }
        $reflect = new \ReflectionClass($class);
        if (!$reflect->isInstantiable()) {
            throw new AppException("path {$class} is not a instance~", 'module_not_instance');
        }
        $ajax   = new $class();
        $method = Config::get('dispatcher.method', 'main');
        if (method_exists($ajax, $method)) {
            $ajax->$method();
        } else {
            throw new AppException("path {$class} invalid", 'module_invalid');
        }
    }
}