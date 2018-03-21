<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/12
 * Time: 16:24
 */

namespace worms\wechat\core;

use worms\core\AppException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Log
 *
 * @package think
 *
 * @method Media uploadImage($filename)
 * @method Media uploadVoice($filename)
 * @method Media uploadVideo($filename)
 * @method Media uploadThumb($filename)
 */
class MediaManage extends Base
{
    /**
     * 多媒体上传。上传图片、语音、视频等文件到微信服务器，上传后服务器会返回对应的media_id，公众号此后可根据该media_id来获取多媒体。
     * 上传的多媒体文件有格式和大小限制，如下：
     * 图片（image）: 1M，支持JPG格式
     * 语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式
     * 视频（video）：10MB，支持MP4格式
     * 缩略图（thumb）：64KB，支持JPG格式
     * 媒体文件在后台保存时间为3天，即3天后media_id失效。
     *
     * @param $filename ，文件绝对路径
     * @param $type     , 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     *
     * desc {"type":"TYPE","media_id":"MEDIA_ID","created_at":123456789}
     *
     * @return Media
     * @throws AppException
     */
    public function upload($filename, $type)
    {
        if (!file_exists($filename)) {
            throw new AppException('文件不存在~', 'FILE_NOT_FUND', $filename);
        }

        //获取ACCESS_TOKEN
        $info = $this->wechat->getHttp()
            ->setUrl('http://file.api.weixin.qq.com/cgi-bin/media/upload')
            ->setOption('multipart', [[
                'name'     => 'media',
                'contents' => fopen($filename, 'r'),
                'filename' => basename($filename),
            ]])
            ->setPrameter('type', $type)
            ->post();

        return new Media($info['type'], $info['media_id'], $info['created_at']);
    }

    /**
     * @desc   __call
     * @author storn
     *
     * @param $name
     * @param $args
     *
     * @return Media
     * @throws AppException
     */
    public function __call($name, $args)
    {
        switch ($name) {
            case 'uploadImage':
                return $this->upload($args[0], 'image');
            case 'uploadVoice':
                return $this->upload($args[0], 'voice');
            case 'uploadVideo':
                return $this->upload($args[0], 'video');
            case 'uploadThumb':
                return $this->upload($args[0], 'thumb');
            default:
                throw new AppException('非法操作,请检查您的代码', 'CHECK_YOUR_CODE', func_get_args());
        }
    }

    /**
     * 获取永久素材
     *
     * @param string $mediaId 多媒体ID
     *
     * @return
     * 返回说明 如果请求的素材为图文消息，则响应如下：
     * {
     * "news_item":
     * [
     * {
     * "title":TITLE,
     * "thumb_media_id"::THUMB_MEDIA_ID,
     * "show_cover_pic":SHOW_COVER_PIC(0/1),
     * "author":AUTHOR,
     * "digest":DIGEST,
     * "content":CONTENT,
     * "url":URL,
     * "content_source_url":CONTENT_SOURCE_URL
     * },
     * ]
     * }
     * 如果返回的是视频消息素材，则内容如下：
     * {
     * "title":TITLE,
     * "description":DESCRIPTION,
     * "down_url":DOWN_URL,
     * }
     * 其他类型的素材消息，则响应的直接为素材的内容，开发者可以自行保存为文件。例如：
     * 示例
     * curl "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=ACCESS_TOKEN" -d '{"media_id":"61224425"}' > file
     *
     *
     */
    public function download($mediaId)
    {
        //获取ACCESS_TOKEN
        $http = $this->wechat->getHttp();

        return $http
            ->setUrl('http://api.weixin.qq.com/cgi-bin/media/get')
            ->setPrameter('media_id', $mediaId)
            ->setOption('on_headers', function (ResponseInterface $response) use ($http) {
                $this->wechat->log($response->getHeaderLine('Content-Type'), 'on-header');
                if ($response->getHeaderLine('Content-Type') != 'text/plain') {
                    $http->withoutJsonDecode()->withoutCheck();
                }
            })
            ->get();
    }
}