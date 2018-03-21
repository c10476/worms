<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/10
 * Time: 23:45
 */

namespace worms\wechat\core;

class Oauth extends Base
{
    const SCOPE_BASE = 'snsapi_base';
    const SCOPE_INFO = 'snsapi_userinfo';

    /**
     * Description: 获取CODE
     *
     * @param string $scope        snsapi_base不弹出授权页面，只能获得OpenId;snsapi_userinfo弹出授权页面，可以获得所有信息
     *                             将会跳转到redirect_uri/?code=CODE&state=STATE 通过GET方式获取code和state
     * @param string $redirect_uri 回调地址
     * @param int    $state        其他参数
     */
    public function getCode($redirect_uri = '', $scope = self::SCOPE_BASE, $state = 1)
    {
        if (empty($redirect_uri)) {
            $redirect_uri = (isset($_SERVER['HTTPS']) ? "https://" : "http://")
                . ($_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_ADDR'])
                . $_SERVER['REQUEST_URI'];
        }
        //公众号的唯一标识
        //授权后重定向的回调链接地址，请使用urlencode对链接进行处理
        $redirect_uri = urlencode($redirect_uri);
        //返回类型，请填写code
        $response_type = 'code';
        //构造请求微信接口的URL
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='
            . $this->wechat->getAppid() . '&redirect_uri=' . $redirect_uri
            . '&response_type=' . $response_type
            . '&scope=' . $scope
            . '&state=' . $state . '#wechat_redirect';
        header('Location: ' . $url, true, 301);
    }

    /**
     * Description: 通过code换取网页授权access_token
     * 首先请注意，这里通过code换取的网页授权access_token,与基础支持中的access_token不同。
     * 公众号可通过下述接口来获取网页授权access_token。
     * 如果网页授权的作用域为snsapi_base，则本步骤中获取到网页授权access_token的同时，也获取到了openid，snsapi_base式的网页授权流程即到此为止。
     *
     * @param string $code 获取的code参数
     *
     * @return array [access_token, expires_in, refresh_token, openid, scope]
     */
    public function getAccessTokenAndOpenId($code)
    {
        //填写为authorization_code
        //构造请求微信接口的URL
        return $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/sns/oauth2/access_token')
            ->setPrameter('appid', $this->wechat->getAppid())
            ->setPrameter('secret', $this->wechat->getAppSecret())
            ->setPrameter('code', $code)
            ->setPrameter('grant_type', 'authorization_code')
            ->get();

    }

    /**
     * 刷新access_token（如果需要）
     * 由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新，refresh_token拥有较长的有效期（7天、30天、60天、90天），当refresh_token失效的后，需要用户重新授权。
     *
     * @param string $refreshToken 通过本类的第二个方法getAccessTokenAndOpenId可以获得一个数组，数组中有一个字段是refresh_token，就是这里的参数
     *
     * @return array(
     * "access_token"=>"网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同",
     * "expires_in"=>access_token接口调用凭证超时时间，单位（秒）,
     * "refresh_token"=>"用户刷新access_token",
     * "openid"=>"用户唯一标识",
     * "scope"=>"用户授权的作用域，使用逗号（,）分隔")
     */
    public function refreshToken($refreshToken)
    {
        return $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/sns/oauth2/refresh_token')
            ->setPrameter('appid', $this->wechat->getAppid())
            ->setPrameter('grant_type', 'refresh_token')
            ->setPrameter('refresh_token', $refreshToken)
            ->get();
    }

    /**
     * 拉取用户信息(需scope为 snsapi_userinfo)
     * 如果网页授权作用域为snsapi_userinfo，则此时开发者可以通过access_token和openid拉取用户信息了。
     *
     * @param string $accessToken 网页授权接口调用凭证。通过本类的第二个方法getAccessTokenAndOpenId可以获得一个数组，数组中有一个字段是access_token，就是这里的参数。注意：此access_token与基础支持的access_token不同
     * @param string $openId      用户的唯一标识
     * @param string $lang        返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     *
     * @return User
     */
    public function getUserInfo($accessToken, $openId, $lang = 'zh_CN')
    {
        $info = $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/sns/userinfo')
            ->setPrameter('openid', $openId)
            ->setPrameter('lang', $lang)
            ->setPrameter('access_token', $accessToken)
            ->withoutAccessToken()
            ->get();

        return new User($info, $this->wechat);
    }

}