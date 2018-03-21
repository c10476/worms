<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/10/12
 * Time: 22:29
 */

namespace worms\image\driver;

use worms\image\Driver;
use worms\image\ImageException;
use worms\image\ImageNode;
use OSS\Core\OssException;
use OSS\OssClient;

class Oss extends Driver
{
    /**
     * @var OssClient
     */
    protected $ossClientObj;

    /**
     * Oss constructor.
     *
     * @param array $options 配置
     *
     * @throws ImageException
     */
    public function __construct($options)
    {
        parent::__construct($options);
        if (!isset($this->options['domain'])) {
            throw new ImageException('图片域名不能为空', "domain_empty");
        }

        if (!isset($this->options['access_key_id'])) {
            throw new ImageException('access_key_id缺失', 'access_key_id_empty');
        }
        if (!isset($this->options['access_secret_key'])) {
            throw new ImageException('access_secret_key不存在', 'access_secret_key_empty');
        }

        if (!isset($this->options['bucket'])) {
            throw new ImageException('空间名不存在', 'budget_empty', $this->options);
        }

        if (!isset($this->options['legal_dir'])) {
            throw new ImageException('合法保存目录列表不存在', 'lagal_dir_empty');
        }
        $this->ossClientObj = new OssClient(
            $this->options['access_key_id'],
            $this->options['access_secret_key'],
            $this->options['end_point'],
            $this->options['is_cname'] == true
        );

    }

    /**
     * @desc   uploadImg
     * @author storn
     *
     * @param string $imgPath 图片地址
     * @param array  $options 其他
     *
     * @return ImageNode
     * @throws ImageException
     */
    public function uploadImg($imgPath = '', $options = [])
    {
        if (empty($imgPath)) {
            $imgPath = $this->getFileTempName();
        }
        if (!isset($options['dir']) || !in_array($options['dir'], $this->options['legal_dir'])) {
            throw new ImageException('图片保存目录不合法', "image_save_dir_invalid", $this->options['legal_dir']);
        }
        if (strpos($imgPath, 'http') === 0) {
            //远程图片
            $imgPath = $this->saveRemoteImg($imgPath, $options);
        }
        if (!is_file($imgPath)) {
            throw new ImageException('图片文件不存在~', "image_file_not_exist");
        }

        list($width, $height, $extIndex) = getimagesize($imgPath);
        $imgExt = $this->getImgExt($extIndex);

        $imgRelativeDir = $this->getImgRelativeDir($imgPath);

        $imgUrl  = "{$options['dir']}/$imgRelativeDir.{$imgExt}";
        $imgSize = filesize($imgPath);

        try {
            $this->ossClientObj->uploadFile($this->options['bucket'], $imgUrl, $imgPath);
        } catch (OssException $e) {
            throw new ImageException($e->getMessage(), $e->getCode(), $this->options);
        }

        return new ImageNode($width, $height, $imgExt, $imgSize, 'http://' . $this->options['domain'] . '/' . $imgUrl);
    }
}