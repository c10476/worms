<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/10 0010
 * Time: 上午 11:00
 */
namespace worms\image;

class ImageNode
{
    private $url;
    private $width;
    private $height;
    private $ext;
    private $size;

    public function __construct($width, $height, $ext, $size, $url)
    {
        $this->width  = $width;
        $this->height = $height;
        $this->ext    = $ext;
        $this->size   = $size;
        $this->url    = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return string
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

}