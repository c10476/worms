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

abstract class Response
{
    // 原始数据
    protected $data = [];

    //状态
    protected $code = 200;

    // 输出参数
    protected $options = [];
    // header参数
    protected $header = [];

    protected $content = null;

    /**
     * @var
     */
    static private $instance;

    /**
     * 架构函数
     *
     * @access   public
     *
     * @param array $options 配置
     */
    public function __construct($options = [])
    {
        $this->options($options);
    }

    /**
     * 创建Response对象
     *
     * @access public
     *
     * @param string $type 响应类型
     *
     * @return Response
     * @throws AppException
     */
    public static function create($type = null)
    {
        is_null($type) && $type = Config::get('response.type', 'json');

        if (is_null(self::$instance[$type])) {
            $class = "worms\\response\\" . ucfirst($type);
            if (class_exists($class)) {
                self::$instance[$type] = new $class(Config::get('response'));
            } else {
                throw new AppException($class . ' MISSING ', 'RESPONSE_CLASS_MISSING');
            }
        }

        return self::$instance[$type];
    }

    /**
     * @desc   error 错误输出
     * @author storn
     *
     * @param string     $msg   错误描述
     * @param string     $errno 错误码
     * @param array|null $data  错误详情
     */
    abstract public function error($msg, $errno, $data = null);

    /**
     * @desc   exception
     * @author storn
     *
     * @param \Throwable $e 异常
     */
    abstract public function exception(\Throwable $e);

    /**
     * 发送数据到客户端
     *
     * @access public
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function send()
    {
        if (!headers_sent() && !empty($this->header)) {
            // 发送状态码
            http_response_code($this->code);
            // 发送头部信息
            foreach ($this->header as $name => $val) {
                header($name . ':' . $val);
            }
        }
        // 处理输出数据
        echo $this->getContent();

        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
    }

    /**
     * 输出的参数
     *
     * @access public
     *
     * @param mixed $options 输出参数
     *
     * @return $this
     */
    public function options($options = [])
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * 输出数据设置
     *
     * @access public
     *
     * @param string $key   key
     * @param mixed  $value 值
     *
     * @return $this
     */
    abstract public function set($key, $value);

    /**
     * 设置响应头
     *
     * @access public
     *
     * @param string|array $name  参数名
     * @param string       $value 参数值
     *
     * @return $this
     */
    public function header($name, $value = null)
    {
        if (is_array($name)) {
            $this->header = array_merge($this->header, $name);
        } else {
            $this->header[$name] = $value;
        }

        return $this;
    }

    /**
     * 发送HTTP状态
     *
     * @param integer $code 状态码
     *
     * @return $this
     */
    public function code($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * LastModified
     *
     * @param string $time
     *
     * @return $this
     */
    public function lastModified($time)
    {
        $this->header['Last-Modified'] = $time;

        return $this;
    }

    /**
     * Expires
     *
     * @param string $time
     *
     * @return $this
     */
    public function expires($time)
    {
        $this->header['Expires'] = $time;

        return $this;
    }

    /**
     * ETag
     *
     * @param string $eTag
     *
     * @return $this
     */
    public function eTag($eTag)
    {
        $this->header['ETag'] = $eTag;

        return $this;
    }

    /**
     * 页面缓存控制
     *
     * @param string $cache 状态码
     *
     * @return $this
     */
    public function cacheControl($cache)
    {
        $this->header['Cache-control'] = $cache;

        return $this;
    }

    /**
     * 页面输出类型
     *
     * @param string $contentType 输出类型
     * @param string $charset     输出编码
     *
     * @return $this
     */
    public function contentType($contentType, $charset = 'utf-8')
    {
        $this->header['Content-Type'] = $contentType . '; charset=' . $charset;

        return $this;
    }

    /**
     * 获取头部信息
     *
     * @param string $name 头部名称
     *
     * @return mixed
     */
    public function getHeader($name = '')
    {
        return !empty($name) ? $this->header[$name] : $this->header;
    }

    /**
     * 获取原始数据
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 获取输出数据
     *
     * @return string
     */
    public function getContent()
    {
        if (null === $this->content) {
            $this->parseContent();
        }

        return $this->content;
    }

    /**
     * @desc   parseContent
     * @author storn
     */
    abstract protected function parseContent();

    /**
     * @desc   setContent
     * @author storn
     *
     * @param string $string 设置输出内容
     *
     * @return $this
     */
    public function setContent($string)
    {
        $this->content = $string;

        return $this;
    }

    /**
     * 获取状态码
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @desc   get
     * @author storn
     *
     * @param null|string $key key
     *
     * @return mixed
     */
    public function get($key = null)
    {
        if (null === $key) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }
}
