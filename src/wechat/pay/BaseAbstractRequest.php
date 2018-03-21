<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/2/8
 * Time: 11:56
 */

namespace worms\wechat\pay;

use GuzzleHttp\Client;
use worms\wechat\core\WechatException;

abstract class BaseAbstractRequest
{
    //请求地址
    const END_POINT = '';

    /** @var  Parameters */
    protected $parameters;

    /** @var  BaseAbstractResponse */
    protected $response;
    protected $httpClient;

    /**
     * BaseAbstractRequest constructor. 初始化
     *
     * @param Client $client http对象
     */
    public function __construct(Client $client)
    {
        $this->httpClient = $client;
        $this->parameters = new Parameters;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->parameters->get('app_id');
    }

    /**
     * @param string $app_id
     *
     * @return BaseAbstractRequest
     */
    public function setAppId($app_id): BaseAbstractRequest
    {
        $this->parameters->set('app_id', $app_id);

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->parameters->get('api_key');
    }

    /**
     * @param string $api_key
     *
     * @return BaseAbstractRequest
     */
    public function setApiKey($api_key): BaseAbstractRequest
    {
        $this->parameters->set('api_key', $api_key);

        return $this;
    }

    /**
     * @return string
     */
    public function getMchId()
    {
        return $this->parameters->get('mch_id');
    }

    /**
     * @param string $mch_id
     *
     * @return BaseAbstractRequest
     */
    public function setMchId($mch_id): BaseAbstractRequest
    {
        $this->parameters->set('mch_id', $mch_id);

        return $this;
    }

    /**
     * Validate the request.
     *
     * This method is called internally by gateways to avoid wasting time with an API call
     * when the request is clearly invalid.
     *
     * @param string ... a variable length list of required parameters
     *
     * @throws WechatException
     */
    public function validate()
    {
        foreach (func_get_args() as $key) {
            $value = $this->parameters->get($key);
            if (!isset($value)) {
                throw new WechatException("The $key parameter is required");
            }
        }
    }

    /**
     * Get all parameters as an associative array.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters->all();
    }

    /**
     * Get a single parameter.
     *
     * @param string $key The parameter key
     *
     * @return mixed
     */
    protected function getParameter($key)
    {
        return $this->parameters->get($key);
    }

    /**
     * Set a single parameter
     *
     * @param string $key   The parameter key
     * @param mixed  $value The value to set
     *
     * @return BaseAbstractRequest Provides a fluent interface
     *
     * @throws WechatException
     */
    protected function setParameter($key, $value)
    {
        if (null !== $this->response) {
            throw new WechatException('Request cannot be modified after it has been sent!');
        }

        $this->parameters->set($key, $value);

        return $this;
    }

    /**
     * @desc   getData
     * @author storn
     * @return mixed
     */
    abstract protected function getData();

    /**
     * @desc   sendData
     * @author storn
     *
     * @param mixed $data
     *
     * @return BaseAbstractResponse
     */
    abstract protected function sendData($data);

    /**
     * @desc   send
     * @author storn
     * @return BaseAbstractResponse
     */
    public function send()
    {
        return $this->sendData($this->getData());
    }

    /**
     * @desc   __call
     * @author storn
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'set') === 0) {
            $arg = substr(Helper::camel2underline($name), 4);
            if ($arg) {
                $this->parameters->set($arg, $arguments[0]);

                return $this;
            }
        } elseif (strpos($name, 'get') === 0) {
            $arg = substr(Helper::camel2underline($name), 4);
            if ($arg) {
                return $this->parameters->get($arg);
            }
        }
    }

    /**
     * @desc   getResponse
     * @author storn
     * @return BaseAbstractResponse
     */
    public function getResponse()
    {
        return $this->response;
    }
}