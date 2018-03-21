<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/12
 * Time: 15:40
 */

namespace worms\wechat\core;

use worms\wechat\Wechat;

class Base
{
    /** @var  Wechat */
    protected $wechat;

    public function __construct(Wechat $wechat)
    {
        $this->wechat = $wechat;
    }

    /**
     * @return Wechat
     */
    public function getWechat()
    {
        return $this->wechat;
    }
}