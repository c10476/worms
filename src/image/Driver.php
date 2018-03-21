<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/15
 * Time: 16:30
 */

namespace worms\image;

use GuzzleHttp\Client;

abstract class Driver
{
    const DIR_DEEP = 2;//目录深度
    //文件控件名称
    private $legal_ext
        = [
            1 => 'gif', 2 => 'jpg', 3 => 'png', 6 => 'bmp',
        ];

    protected $options = [
        'tmp_dir' => '/tmp/',
    ];

    /**
     * Driver constructor. 构造函数
     *
     * @param array $options 配置
     */
    public function __construct($options)
    {
        $this->setOptions($options);
    }

    /**
     * 获取图片的相对路径
     *
     * @param string $imgPath 图片相对路径
     *
     * @return string
     */
    protected function getImgRelativeDir($imgPath)
    {
        $md5file = md5_file($imgPath);
        $dir     = '';
        for ($i = 1; $i <= self::DIR_DEEP; $i++) {
            $dir .= substr($md5file, ($i - 1) * 2, 2) . '/';
        }

        return $dir . $md5file;
    }

    /**
     * 获取图片的格式 后缀名
     *
     * @param $extIndex
     *
     * @return mixed
     * @throws ImageException
     */
    protected function getImgExt($extIndex)
    {
        if (!isset($this->legal_ext[$extIndex])) {
            throw new ImageException('不支持该格式图片的上传[' . $extIndex . ']', 7600);
        }

        return $this->legal_ext[$extIndex];
    }

    /**
     * 保存远程图片
     *
     * @param string $imgUrl  远程图片地址
     * @param array  $options 配置
     *
     * @return string
     * @throws ImageException
     */
    protected function saveRemoteImg($imgUrl, $options = [])
    {
        $client = new Client();
        try {
            $imgContent = $client->get($imgUrl, $options)->getBody()->getContents();
            $tmpFile    = uniqid();
            if (file_put_contents($tmpFile, $imgContent)) {
                return $tmpFile;
            }
        } catch (\Exception $e) {
            throw new ImageException("保存远程图片失败~", "download_remote_img_failed", $e);
        }

    }

    /**
     * 上传图片
     *
     * @param string $imgPath 图片地址 、图片远程地址、本地图片
     * @param array  $options 上传配置
     *
     * @return ImageNode
     */
    abstract public function uploadImg($imgPath = '', $options = []);

    /**
     * createTmpImgFromBase64 保存base64格式的图片内容 （包含头部）
     *
     * @author storn
     *
     * @param string $data 图片信息
     *
     * @return string
     * @throws ImageException
     */
    public function createTmpImgFromBase64($data)
    {
        $tmp_path = $this->options['tmp_dir'] . uniqid();
        if (!preg_match('/^data:\s*image\/\w+;base64,([\w\+\/=_]+)$/', $data, $match)) {
            throw new ImageException("base64格式图片内容非法~", "invalid_base64_content");
        }
        $rs = file_put_contents($tmp_path, base64_decode(str_replace('_', '+', $match[1])));
        if ($rs === false) {
            throw  new ImageException("保存临时图片失败~", 'save_img_failed');
        }

        return $tmp_path;
    }

    /**
     * 获取文件空间里面的临时文件
     *
     * @return string
     * @throws ImageException
     */
    protected function getFileTempName()
    {
        if ($_FILES) {
            switch ($_FILES[$this->options['input_name']]['error']) {
                case 1:
                case 2:
                    throw new ImageException('图片大小超出限制', 'img_size_limit');
                case 3:
                    throw new ImageException('图片只有部分被上传', 'not_fullly_uploaded');
                case 4:
                    throw new ImageException('没有图片被上传', 'no_image_uploaded');
                case 5:
                    throw new ImageException('上传文件大小为0', 'image_empty');
                case 0:
            }
        } else {
            throw new ImageException('没接受到上传文件~', 'no_image_file');
        }

        return $_FILES[$this->options['input_name']]['tmp_name'];
    }

    /**
     * @desc   setOptions 设置配置
     * @author storn
     *
     * @param array $options
     */
    protected function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }
}