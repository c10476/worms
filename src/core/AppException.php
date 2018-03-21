<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/12
 * Time: 11:24
 */
namespace worms\core;

class AppException extends \Exception
{
    protected $errno;
    protected $detail;

    /**
     * AppException constructor.
     *
     * @param mixed $msg    错误详情
     * @param string $errno  错误英文码
     * @param array  $detail 错误详情
     */
    public function __construct($msg, $errno = 'ERROR', $detail = [])
    {
        if (is_array($msg)) {
            list($msg, $errno, $detail) = $msg;
        }
        $this->errno  = strtoupper($errno);
        $this->detail = $detail;
        parent::__construct((string)$msg, (int)$errno);
    }

    /**
     * @return string
     */
    public function getErrno()
    {
        return $this->errno;
    }

    /**
     * @desc   getDetail 获取异常详情数组
     * @author storn
     * @return array
     */
    public function getDetail()
    {
        return $this->detail;
    }
}
