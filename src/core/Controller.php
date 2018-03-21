<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/17
 * Time: 11:09
 */

namespace worms\core;

use worms\response\View;

abstract class Controller extends ParseParams
{
    //接口请求开始时间
    protected $_start_time;
    protected $options = [];
    /** @var  View */
    private $reponse;
    /**
     * @var array 接口返回数据数组
     */
    protected $return = [];

    public function beforeRun()
    {
        //自定义初始化 自定义参数
        $this->init();
        //解析获取参数
        $this->parse();
        $this->reponse = Response::create();

        $classPath = str_replace('\\', DIRECTORY_SEPARATOR,
            str_replace(Config::get('dispatcher.namespace') . '\\', '', static::class)
        );
        $this->reponse->setIndex($classPath . $this->options['ext']);

        $this->reponse->setPath($this->options['tpl_path']);
    }

    /**
     * @desc   init 初始化
     * @author storn
     */
    abstract protected function init();

    /**
     * @desc   run 主线程
     * @author storn
     * @return void
     */
    abstract public function run();

    /**
     * @desc   main 主线程
     * @author storn
     */
    final public function main()
    {
        $this->beforeRun();
        $this->run();
        $this->send();
    }

    /**
     * FrontApi constructor. 构造函数
     */
    public function __construct()
    {
        $this->_start_time = microtime(true);
        $this->options     = array_merge($this->options, Config::get('controller', []));
    }

    /**
     * @desc   error 错误输出
     * @author storn
     *
     * @param string $msg  错误字符串
     * @param string $code 错误码
     */
    protected function error($msg, $code)
    {
        $this->set('code', $code)
            ->set('msg', $msg)
            ->send();
    }

    /**
     * @desc   set 设置返回参数
     * @author storn
     *
     * @param string $key   key
     * @param mixed $value value
     *
     * @return $this
     */
    final protected function set($key, $value)
    {
        $this->reponse->set($key, $value);

        return $this;
    }

    /**
     * @desc   _beforeOutput 输出数据之前的拦截器
     * @author storn
     */
    protected function beforeResponse()
    {
    }

    /**
     * @desc   output
     * @author storn
     *
     */
    final private function send()
    {
        $this->beforeResponse();
        $this->reponse->send();
        $this->afterResponse();
    }

    /**
     * @desc   afterRequest 自定义结束拦截器
     * @author storn
     */
    protected function afterResponse()
    {
    }

    /**
     * @desc   getResponse
     * @author storn
     * @return View
     */
    protected function getResponse()
    {
        return $this->reponse;
    }
}