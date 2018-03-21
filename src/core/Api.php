<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/14
 * Time: 11:04
 */

namespace worms\core;

abstract class Api extends ParseParams
{
    //是否开始调试
    const OPT_DEBUG = 'debug';
    //是否格式化接口输出数据 接口返回只有string和boolean类型
    const OPT_FORMAT_TO_STRING = 'format_to_string';
    //接口默认返回code
    const OPT_DEFAULT_CODE = 'default_code';
    //接口默认返回提交消息
    const OPT_DEFAULT_MSG = 'default_msg';

    //接口请求开始时间
    protected $_start_time;
    //参数定义
    protected $options = [
        self::OPT_DEBUG            => false,
        self::OPT_FORMAT_TO_STRING => true,
        self::OPT_DEFAULT_CODE     => 'SUCCESS',
        self::OPT_DEFAULT_MSG      => 'SUCCESS',
    ];
    /**
     * @var array 接口返回数据数组
     */
    protected $return = [];
    /** @var  Response */
    private $response;

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
     * @desc   beforeRun
     * @author storn
     */
    protected function beforeRun()
    {
        //自定义初始化 自定义参数
        $this->init();
        //解析获取参数
        $this->parse();
        $this->set('code', $this->options[self::OPT_DEFAULT_CODE]);
        $this->set('msg', $this->options[self::OPT_DEFAULT_MSG]);
    }

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
        set_exception_handler([$this, 'exceptionHandler']);
        $this->_start_time = microtime(true);
        $this->options     = array_merge($this->options, Config::get('api', []));
    }

    /**
     * @desc   exceptionHandler 异常拦截
     * @author storn
     *
     * @param \Throwable $e
     */
    public function exceptionHandler(\Throwable $e)
    {
        if ($this->getOption(self::OPT_DEBUG)) {
            $this
                ->set('exception', get_class($e))
                ->set('trace', array_merge(
                        ["@{$e->getFile()} +{$e->getLine()}"]
                        , explode("\n", $e->getTraceAsString()))
                );
            method_exists($e, 'getDetail')
            &&
            $this->set('detail', $e->getDetail());
        }
        $errno = method_exists($e, 'getErrno') ? $e->getErrno() : 'ERROR';

        $this
            ->set('msg', $e->getMessage())
            ->set('code', $errno)
            ->send();
        App::handleException($e);
    }

    /**
     * @desc   set 设置返回参数
     * @author storn
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return $this
     */
    final protected function set($key, $value)
    {
        if (strpos($key, '.') > 0) {
            list($pK, $k) = explode('.', $key);
            $this->return[$pK][$k] = $value;
        } else {
            $this->return[$key] = $value;
        }

        return $this;
    }

    /**
     * @desc   _beforeOutput 输出数据之前的拦截器
     * @author storn
     */
    protected function beforeResponse()
    {
        $this->debug('cost_time', sprintf('%.6f', microtime(true) - $this->_start_time));
        $this->debug('cost_mem', sprintf('%.6f', memory_get_usage(true) / 1048576));
    }

    /**
     * @desc   output
     * @author storn
     *
     */
    final private function send()
    {
        $this->beforeResponse();
        $this->response = Response::create();
        $this->response->options([self::OPT_FORMAT_TO_STRING => $this->options[self::OPT_FORMAT_TO_STRING]]);
        foreach ($this->return as $key => $value) {
            $this->response->set($key, $value);
        }
        $this->response->send();
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
     * @desc   debug
     * @author storn
     *
     * @param string $key   key
     * @param mixed  $value 值
     */
    protected function debug($key, $value)
    {
        if ($this->options[self::OPT_DEBUG]) {
            $this->set('debug.' . $key, $value);
        }
    }

    /**
     * @desc   option 配置
     * @author storn
     *
     * @param string $key   key
     * @param mixed  $value value
     */
    public function option($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * @desc   getOption
     * @author storn
     *
     * @param string $key key
     *
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->options[$key];
    }

    /**
     * @desc   getResponse
     * @author storn
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

}