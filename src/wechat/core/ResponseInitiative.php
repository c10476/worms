<?php
namespace worms\wechat\core;

use worms\wechat\Wechat;

/**
 * 发送主动响应
 * Created by Lane.
 * User: lane
 * Date: 13-12-29
 * Time: 下午5:54
 * Mail: lixuan868686@163.com
 * Website: http://www.lanecn.com
 */
class ResponseInitiative extends Base
{

    const REQUEST_URL = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=';

    protected static $action = 'POST';

    /**
     * @descrpition 文本
     *
     * @param  string $toUserName 回复的用户openid
     * @param  string $content    回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示）
     *
     * @return string
     */
    public function text($toUserName, $content)
    {

        //开始
        $template = [
            'touser'  => $toUserName,
            'msgtype' => 'text',
            'text'    => [
                'content' => $content,
            ],
        ];

        return $this->post($template);
    }

    /**
     * @descrpition 图片
     *
     * @param string  $toUserName 接受者
     * @param  string $mediaId    通过上传多媒体文件，得到的id。
     *
     * @return string
     */
    public function image($toUserName, $mediaId)
    {
        //开始
        $template = [
            'touser'  => $toUserName,
            'msgtype' => 'image',
            'image'   => [
                'media_id' => $mediaId,
            ],
        ];

        return $this->post($template);
    }

    /**
     * @descrpition 语音
     *
     * @param string $toUserName 接受者
     * @param string $mediaId    通过上传多媒体文件，得到的id
     *
     * @return string
     */
    public function voice($toUserName, $mediaId)
    {

        //开始
        $template = [
            'touser'  => $toUserName,
            'msgtype' => 'voice',
            'voice'   => [
                'media_id' => $mediaId,
            ],
        ];

        return $this->post($template);
    }

    /**
     * @descrpition 视频
     *
     * @param string $toUserName  接受者
     * @param string $mediaId     通过上传多媒体文件，得到的id
     * @param string $title       标题
     * @param string $description 描述
     *
     * @return string
     */
    public function video($toUserName, $mediaId, $title, $description)
    {

        //开始
        $template = [
            'touser'  => $toUserName,
            'msgtype' => 'video',
            'video'   => [
                'media_id'    => $mediaId,
                'title'       => $title,
                'description' => $description,
            ],
        ];

        return $this->post($template);
    }

    /**
     * @descrpition 音乐
     *
     * @param string $toUserName   接受者id
     * @param string $title        标题
     * @param string $description  描述
     * @param string $musicUrl     音乐链接
     * @param string $hqMusicUrl   高质量音乐链接，WIFI环境优先使用该链接播放音乐
     * @param string $thumbMediaId 缩略图的媒体id，通过上传多媒体文件，得到的id
     *
     * @return string
     */
    public function music($toUserName, $title, $description, $musicUrl, $hqMusicUrl, $thumbMediaId)
    {

        //开始
        $template = [
            'touser'  => $toUserName,
            'msgtype' => 'music',
            'music'   => [
                'title'          => $title,
                'description'    => $description,
                'musicurl'       => $musicUrl,
                'hqmusicurl'     => $hqMusicUrl,
                'thumb_media_id' => $thumbMediaId,
            ],
        ];

        return $this->post($template);
    }

    /**
     * @descrpition 图文消息 - 单个项目的准备工作，用于内嵌到self::news()中。现调用本方法，再调用self::news()
     *              多条图文消息信息，默认第一个item为大图,注意，如果调用本方法得到的数组总项数超过10，则将会无响应
     *
     * @param string $title       标题
     * @param string $description 描述
     * @param string $picUrl      图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200
     * @param string $url         点击图文消息跳转链接
     *
     * @return string
     */
    public function newsItem($title, $description, $picUrl, $url)
    {
        return $template = [
            'title'       => $title,
            'description' => $description,
            'url'         => $url,
            'picurl'      => $picUrl,
        ];
    }

    /**
     * @descrpition 图文 - 先调用self::newsItem()再调用本方法
     *
     * @param string $toUserName 接收人
     * @param array  $itemsArray 数组，每个项由self::newsItem()返回
     *
     * @return string
     */
    public function news($toUserName, $itemsArray)
    {
        //开始
        $template = [
            'touser'  => $toUserName,
            'msgtype' => 'news',
            'news'    => [
                'articles' => $itemsArray,
            ],
        ];

        return $this->post($template);

    }

    /**
     * @desc   post
     * @author storn
     *
     * @param array $template 消息模板数据
     *
     * @return string
     */
    protected function post($template)
    {
        $this->wechat->log($template, 'reponse-initiative');

        return $this->wechat->getHttp()
            ->setUrl(self::REQUEST_URL)
            ->post($template);
    }
}