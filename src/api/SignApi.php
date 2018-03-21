<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/2
 * Time: 20:40
 */

namespace worms\api;

use worms\core\Api;
use worms\core\ApiParams;
use worms\core\AppException;

abstract class SignApi extends DenyResubmitApi
{
    const OPT_WITHOUT_CHECK_SIGN = 'without_check_sign';
    const GLOBAL_PARAMS = ['V', 'F', 'noncestr', 'auth'];
    const SIGN_PARAM = 'sign';

    public function __construct()
    {
        $this->options[self::OPT_WITHOUT_CHECK_SIGN] = false;
        $this->addParams(static::GLOBAL_PARAMS);
        $this->addParam(self::SIGN_PARAM);

        parent::__construct();
    }

    /**
     * @desc   beforeRun
     * @author storn
     */
    protected function beforeRun()
    {
        parent::beforeRun();
        if (!$this->options[self::OPT_WITHOUT_CHECK_SIGN] && $this->makeSign() != $this->get(static::SIGN_PARAM)->getValue()) {
            throw new ApiException('签名错误', 'SIGNATURE_ERROR');
        }
    }

    /**
     * @desc   withoutCheckSign
     * @author storn
     */
    protected function withoutCheckSign()
    {
        $this->options[self::OPT_WITHOUT_CHECK_SIGN] = true;
    }

    /**
     * @desc   makeSign
     * @author storn
     * @return string
     */
    abstract protected function makeSign();
}