<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/11
 * Time: 00:16
 */

namespace worms\wechat\core;

use worms\wechat\Wechat;

class User extends Base
{
    protected $subscribe = 0;
    protected $openid;
    protected $nickname;
    protected $sex;
    protected $province;
    protected $city;
    protected $country;
    protected $headimgurl;
    protected $privilege;
    protected $unionid;
    protected $remark;
    protected $subscribe_time;

    /**
     * User constructor.
     *
     * @param array  $data   数组
     * @param Wechat $wechat 微信对象
     */
    public function __construct($data, Wechat $wechat)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
        parent::__construct($wechat);
        $this->wechat->log($data, 'userinfo');
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @return string
     */
    public function getOpenid()
    {
        return $this->openid;
    }

    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @return string
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getHeadimgurl()
    {
        return $this->headimgurl;
    }

    /**
     * @return array
     */
    public function getPrivilege()
    {
        return $this->privilege;
    }

    /**
     * @return string
     */
    public function getUnionid()
    {
        return $this->unionid;
    }

    /**
     * @return bool
     */
    public function isSubscribe()
    {
        return $this->subscribe == 1;
    }

    /**
     * @return int
     */
    public function getSubscribeTime()
    {
        return $this->subscribe_time;
    }

}