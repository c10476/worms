<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/12
 * Time: 21:01
 */

namespace storn\worms\tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\Setup;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use worms\core\App;
use worms\core\AppException;
use worms\core\Cache;
use worms\core\Config;
use worms\core\Db;
use worms\core\Log;
use worms\entity\PmsStore;
use Symfony\Component\Config\Definition\Exception\Exception;

class curlTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $client = new Client();
        try {
            $reponse = $client->get('http://admin.xzb.chenmm.cn/common/uploadimg');
            var_dump($reponse->getBody()->getContents());
        } catch (Exception $e) {
            var_dump(get_class($e));
        }
    }

    public function test1(){

    }

}
