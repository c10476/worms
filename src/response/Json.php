<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/16
 * Time: 22:28
 */

namespace worms\response;

use worms\core\AppException;
use worms\core\Response;

class Json extends Response
{
    /**
     * Json constructor.
     *
     * @param array $options 初始化
     */
    public function __construct(array $options = [])
    {
        $options = array_merge([
            'json_encode_param' => JSON_UNESCAPED_UNICODE,
            'default_code'      => 'SUCCESS',
            'default_msg'       => 'SUCCESS',
            'format_to_string'  => true,
        ], $options);
        parent::__construct($options);
        $this->set('code', $this->options['default_code']);
        $this->set('msg', $this->options['default_msg']);
        $this->contentType('application/json');
    }

    /**
     * @desc   error 错误输出
     * @author storn
     *
     * @param string     $msg   错误描述
     * @param string     $errno 错误码
     * @param array|null $data  错误详情
     */
    public function error($msg, $errno, $data = null)
    {
        $this->set('code', strtoupper($errno))
            ->set('msg', $msg)
            ->set('data', $data)
            ->send();
    }

    /**
     * @desc   exception 异常截获
     * @author storn
     *
     * @param \Throwable $e 异常对象
     */
    public function exception(\Throwable $e)
    {
        if ($e instanceof AppException) {
            $this->set('code', $e->getErrno());
        } else {
            $this->set('code', $e->getCode());
        }
        $this
            ->set('msg', $e->getMessage())
            ->set('data', array_merge(
                    ["@{$e->getFile()} +{$e->getLine()}"]
                    , explode("\n", $e->getTraceAsString()))
            )
            ->send();
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
    public function set($key, $value)
    {
        if (is_null($value)) {
            unset($this->data[$key]);
        } else {
            $this->data[$key] = $this->options['format_to_string'] ? $this->formatToString($value) : $value;
        }

        return $this;
    }

    /**
     * @desc   formatToString 所有类型数据格式化成字符串
     * @author storn
     *
     * @param mixed $value 待格式化数据
     *
     * @return array|string
     */
    private function formatToString($value)
    {
        if (is_array($value)) {
            $tmp = [];
            foreach ($value as $k => $v) {
                $tmp[$k] = $this->formatToString($v);
            }

            return $tmp;
        } elseif (!is_bool($value)) {
            return (string)$value;
        }

        return $value;
    }

    /**
     * 渲染输出数据
     */
    public function parseContent()
    {
        try {
            // 返回JSON数据格式到客户端 包含状态信息
            $this->content = json_encode(
                $this->data
                , $this->options['json_encode_param']
            );

            if ($this->content === false) {
                throw new \InvalidArgumentException(json_last_error_msg());
            }

        } catch (\Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }
            throw $e;
        }
    }
}