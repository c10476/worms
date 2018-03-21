<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/10 0010
 * Time: 上午 10:58
 */
namespace worms\core;

use worms\image\Driver;
use worms\image\ImageException;

class Image
{
    static private $image = [];

    /**
     * @param string $ini 配置
     *
     * @return Driver
     * @throws ImageException
     */
    static public function provide($ini = 'default')
    {
        if (!isset(self::$image[$ini])) {
            $conf = Config::get('image.' . $ini);
            if (empty($conf)) {
                throw new ImageException('图片上传配置不存在', "image_conf_empty");
            }
            $driver = '\\worms\\image\\driver\\' . ucfirst($conf['type']);

            self::$image[$ini] = new $driver($conf);
        }

        return self::$image[$ini];
    }
}
