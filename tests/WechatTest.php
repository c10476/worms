<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/10
 * Time: 21:22
 */

namespace storn\worms\tests;

use worms\core\Cache;
use worms\core\Config;
use worms\wechat\cache\CacheProvider;
use worms\wechat\core\TemplateMsg;
use worms\wechat\core\UserManage;
use worms\wechat\log\EchologProvider;
use worms\wechat\Wechat;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Mapping\CascadingStrategy;

class WechatTest extends TestCase
{
    public function setUp()
    {

        parent::setUp(); // TODO: Change the autogenerated stub

        Config::batchSet([
            'cache'  => [
                'type'    => 'complex',
                'default' => [
                    'type'          => "file",
                    'expire'        => 0,
                    'cache_subdir'  => false,
                    'prefix'        => '123',
                    'path'          => '.',
                    'data_compress' => false,
                ],
            ],
            'wechat' => [
                'config' => [
                    'appid'      => 'wxd2c4732b5f9cd99d',
                    'app_secret' => '3719c70d5a101b4255586ce6f45a98a3',
                    'app_token'  => 'yuqi_weixin',
                    'app_aeskey' => 'MqAuKoex6FyT5No0OcpRy22cThGs0P1vz4mJ2gwvvkF',
                    'debug'      => true,
                ],
            ],
        ]);
    }

    public function testMedia()
    {
        $wechat = new Wechat(Config::get('wechat'), new CacheProvider());
        $wechat->setLogger(new EchologProvider());
        $openid      = 'o-ZuSwW63xq-iDUQFP6pSLKCW6Xw';
        $userManager = new UserManage($wechat);
        $data        = $userManager->getGroupByOpenId($openid);
        print_r($data);
        $user = $userManager->getUser($openid);
        $this->assertEquals($openid, $user->getOpenid());
    }

    public function testTemplateMsg()
    {
        $wechat = new Wechat(Config::get('wechat'), new CacheProvider());
        $wechat->setLogger(new EchologProvider());
        $openid = 'o-ZuSwW63xq-iDUQFP6pSLKCW6Xw';

        $tem = new TemplateMsg($wechat);
        $tem->sendTemplateMessage([], $openid, 1231212, '');
    }
}