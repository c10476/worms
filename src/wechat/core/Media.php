<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/12
 * Time: 16:25
 */

namespace worms\wechat\core;

class Media
{
    private $type;
    private $media_id;
    private $create_at;

    /**
     * Media constructor. 构造函数
     *
     * @param string $type       多媒体类型
     * @param string $media_id   资源id
     * @param string $created_at 创建时间
     */
    public function __construct($type, $media_id, $created_at)
    {
        $this->type      = $type;
        $this->media_id  = $media_id;
        $this->create_at = $created_at;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getMediaId()
    {
        return $this->media_id;
    }

    /**
     * @return string
     */
    public function getCreateAt()
    {
        return $this->create_at;
    }

}