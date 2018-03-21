<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/12
 * Time: 23:57
 */

namespace worms\wechat\core;

class Http extends Base
{
    protected $url = '';
    protected $options = [
        'query'   => [],
        'timeout' => 5,
    ];
    protected $withAccessToken = true;
    protected $withCheck = true;
    protected $withJsonDecode = true;

    /**
     * @return array
     */
    public function getQuerys()
    {
        return $this->options['query'];
    }

    /**
     * @param array $querys 参数列表
     *
     * @return Http
     */
    public function setQuerys(array $querys)
    {
        $this->options['query'] = $querys;

        return $this;
    }

    /**
     * @desc   setPrameter
     * @author storn
     *
     * @param string $key   参数
     * @param mixed  $value 参数值
     *
     * @return Http
     */
    public function setPrameter($key, $value)
    {
        $this->options['query'][$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return Http
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * 配置
     *
     * @param string $key   key
     * @param mixed  $value 值
     *
     * @return Http
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @desc 不传递accessToken
     * @return Http
     */
    public function withoutAccessToken()
    {
        $this->withAccessToken = false;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Http
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @desc   post
     * @author storn
     *
     * @param array $params post参数
     *
     * @return mixed
     * @throws WechatException
     */
    public function post(array $params = [])
    {
        return $this->setOption('body', json_encode($params, JSON_UNESCAPED_UNICODE))
            ->request('post');
    }

    /**
     * @desc   get
     * @author storn
     *
     * @return array
     */
    public function get()
    {
        return $this->request('get');
    }

    /**
     * @desc   withoutCheck 请求结果不需要检验
     * @author storn
     * @return Http
     */
    public function withoutCheck()
    {
        $this->withCheck = false;

        return $this;
    }

    /**
     * @desc   withoutJsonDecode 请求结果不需要json_decode
     * @author storn
     * @return Http
     */
    public function withoutJsonDecode()
    {
        $this->withJsonDecode = false;

        return $this;
    }

    /**
     * @desc   request
     * @author storn
     *
     * @param string $type 请求类型
     *
     * @return array
     * @throws WechatException
     */
    private function request($type)
    {
        $this->wechat->log($this->url, 'request-url');

        if ($this->withAccessToken) {
            $this->setPrameter('access_token', $this->wechat->getAccessToken());
        }

        $metadata = $reponse = $this->wechat->getClient()
            ->request($type, $this->url, $this->options)
            ->getBody()
            ->getContents();

        $this->withJsonDecode && $reponse = json_decode($reponse, true);

        if (json_last_error()) {
            throw new WechatException("请求数据异常", 'GET_URL_INVALID', [$this->url, $metadata]);
        }
        if ($this->withCheck && $reponse['errcode'] && $reponse['errmsg']) {
            throw new WechatException($reponse['errmsg'], $reponse['errcode'], $reponse);
        }

        return $reponse;
    }
}