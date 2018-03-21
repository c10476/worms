<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/14
 * Time: 11:23
 */

namespace worms\core;

use worms\api\ApiException;
use worms\api\ParameterException;

class ApiParams implements Params
{

    protected $key = '';
    //参数传递方式
    protected $method;
    //参数是否必传
    protected $is_require = true;
    //参数类型
    protected $type;
    //参数如果没有传递的话 是否设置默认值
    protected $default;
    //参数验证规则类型
    protected $validate_type = self::VALIDATE_TYPE_COMMON;
    //验证规则的值
    protected $validate_value;
    //参数的值
    protected $value;

    /**
     * @var string 全局默认传参方式
     */
    static private $globalMethod = self::METHOD_REQUEST;
    /**
     * @var array 异常
     */
    protected $exception = [
        'require'  => null,
        'validate' => null,
    ];

    /**
     * ApiParams constructor. 构造函数
     *
     * @param string $key  参数名称
     * @param string $type 参数类型
     */
    public function __construct($key, $type = self::TYPE_STRING)
    {
        $key && $this->setKey($key);
        $type && $this->setType($type);
    }

    /**
     * @desc   create
     * @author storn
     *
     * @param string $key  key
     * @param string $type 类型
     *
     * @return ApiParams
     */
    static public function create($key, $type = self::TYPE_STRING)
    {
        $obj = new self($key, $type);

        return $obj;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @desc 参数名称
     *
     * @param string $key 参数名称
     *
     * @return ApiParams
     * @throws AppException
     */
    public function setKey($key)
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z_0-9]*$/', $key)) {
            throw new AppException(['接口参数名称非法', 'API_PARAMS_INVALID']);
        }
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        if (is_null($this->method)) {
            $this->setMethod(self::$globalMethod);
        }

        return $this->method;
    }

    /**
     * @desc 设置参数传递方式3
     *
     * @param string $method 设置参数传递方式
     *
     * @return ApiParams
     * @throws AppException
     */
    public function setMethod($method)
    {
        if (
        !in_array($method, [
            self::METHOD_COOKIE,
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_REQUEST,
            self::METHOD_BODY,
        ])
        ) {
            throw new AppException("{$method}:参数传递方式非法", "PARAM_METHOD_INVALID");
        }
        $this->method = $method;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRequire()
    {
        return $this->is_require == true;
    }

    /**
     * @param boolean|\Exception|array $exception 设置参数是否必传 false 非必传 true 必传 其他则为异常
     *
     * @return ApiParams
     */
    public function setRequire($exception)
    {
        if ($exception === false) {
            $this->is_require = false;
        }
        if (!is_bool($exception))
            $this->setRequireException($exception);

        return $this;
    }

    /**
     * @desc 获取默认值
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @desc 设置默认值
     *
     * @param mixed $default 默认值
     *
     * @return ApiParams
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return string
     */
    public function getValidateType()
    {
        return $this->validate_type;
    }

    /**
     * @获取验证值
     *
     * @return mixed
     */
    public function getValidateValue()
    {
        return $this->validate_value;
    }

    /**
     * @desc 设置验证规则
     *
     * @param mixed  $validate_value
     * @param string $validate_type
     *
     * @return ApiParams
     * @throws AppException
     */
    public function setValidate($validate_value, $validate_type = self::VALIDATE_TYPE_COMMON)
    {

        $this->validate_type  = $validate_type;
        $this->validate_value = $validate_value;
        switch ($validate_type) {
            case self::VALIDATE_TYPE_COMMON:
                break;
            case self::VALIDATE_TYPE_IN:
                if (!is_array($validate_value)) {
                    throw new AppException("参数范围值应该是一个数组", "PARAM_SHOULD_BE_ARRAY");
                }
                break;
            case self::VALIDATE_TYPE_REG:
                break;
            default:
                $this->validate_type = null;
                throw new AppException("参数验证类型不合法", 'PARAM_VALIDATE_TYPE_INVALID');
        }

        return $this;
    }

    /**
     * @desc   parse
     * @author storn
     */
    public function parse()
    {
        $this->searchValue();
        if (is_null($this->value)) {
            if ($this->isRequire())
                throw $this->specialException('require') ?? (ParameterException::notAllowNull($this));
        } else {
            $this->formatValue();
            $this->validate();
        }

    }

    /**
     * @desc   formatValue 格式化值
     * @author storn
     * @throws ApiException
     */
    private function formatValue()
    {
        switch ($this->type) {

            case self::TYPE_FLOAT:
                if (!Validate::isFloat($this->value)) {
                    throw $this->specialException('validate') ?? (ParameterException::typeInvalid($this));
                }
                $this->value = (float)$this->value;
                break;
            case self::TYPE_INT:
                if (!Validate::isInt($this->value)) {
                    throw $this->specialException('validate') ?? (ParameterException::typeInvalid($this));
                }
                $this->value = (int)$this->value;
                break;
            case self::TYPE_JSON:
                json_decode($this->value);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw $this->specialException('validate') ?? (ParameterException::typeInvalid($this));
                }
                break;
            case self::TYPE_ARRAY:
                if (!is_array($this->value)) {
                    throw $this->specialException('validate') ?? (ParameterException::typeInvalid($this));
                }
                break;
            default:
                $this->value = (string)$this->value;
        }
    }

    /**
     * @desc   searchValue 获取值
     * @author storn
     */
    private function searchValue()
    {
        $from = null;
        switch ($this->getMethod()) {
            case self::METHOD_REQUEST:
                $from = $_REQUEST;
                break;
            case self::METHOD_POST:
                $from = $_POST;
                break;
            case self::METHOD_GET:
                $from = $_GET;
                break;
            case self::METHOD_COOKIE:
                $from = $_COOKIE;
                break;
            case self::METHOD_BODY:
                $this->value = file_get_contents(Config::get('body_stream', 'php://input'));

                return;
            default:
                return;
        }
        $this->value = $from[$this->key] ?? null;
    }

    /**
     * @desc   validate 验证参数合法性
     * @author storn
     * @throws ApiException
     */
    private function validate()
    {
        $value = $this->validate_value;
        switch ($this->validate_type) {
            case self::VALIDATE_TYPE_COMMON:
                if (
                    ($value instanceof \Closure && !$value($this->value))
                    OR
                    (
                        method_exists(Validate::class, $value)
                        &&
                        Validate::$value($this->value) === false
                    )
                ) {
                    throw $this->specialException('validate')??(ParameterException::simpleValidateFailed($this));
                }
                break;
            case self::VALIDATE_TYPE_IN:

                if (!in_array($this->value, $value)) {
                    throw $this->specialException('validate') ?? ParameterException::typeInvalid($this);
                }
                break;
            case self::VALIDATE_TYPE_REG:
                if (!preg_match($value, $this->value)) {
                    throw $this->specialException('validate') ?? (ParameterException::regValidateFailed($this));
                }
                break;
            default:
        }
    }

    /**
     * @desc   add
     * @author storn
     *
     * @param Api $api
     */
    public function add(Api $api)
    {
        $api->addParam($this);
    }

    /**
     * @desc   setRequireException
     * @author storn
     *
     * @param array|\Exception|string $exception 异常
     *
     * @return $this
     */
    public function setRequireException($exception)
    {
        $this->setException($exception, 'require');

        return $this;
    }

    /**
     * @desc   setValidateException
     * @author storn
     *
     * @param array| string |\Exception $exception 异常
     *
     * @return $this
     */
    public function setValidateException($exception)
    {
        $this->setException($exception, 'validate');

        return $this;
    }

    /**
     * @desc   setException 设置异常
     * @author storn
     *
     * @param array|\Exception|string $exception 异常
     * @param string                  $type      异常类型
     */
    protected function setException($exception, $type)
    {
        if ($exception instanceof \Exception) {
            $this->exception[$type] = $exception;
        } else {
            $this->exception[$type] = new ApiException($exception);
        }
    }

    /**
     * @desc   getException 获取异常
     * @author storn
     *
     * @param string $type 异常类型
     *
     * @return \Exception|null
     */
    protected function specialException($type)
    {
        if (isset($this->exception[$type]) && $this->exception[$type] instanceof \Exception) {
            return $this->exception[$type];
        }

        return null;
    }

    /**
     * @desc   setDefaultMethod
     * @author storn
     *
     * @param string $method
     */
    static public function setGlobalMethod($method)
    {
        if (in_array($method, [self::METHOD_REQUEST, self::METHOD_COOKIE, self::METHOD_GET, self::METHOD_POST])) {
            self::$globalMethod = $method;
        }
    }
}