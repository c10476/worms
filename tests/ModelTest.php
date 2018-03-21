<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/2
 * Time: 19:14
 */

namespace storn\worms\tests;

use worms\core\Config;
use worms\core\Db;
use worms\core\Model;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function setUp()
    {

        //table test ddl
        /*
         * CREATE TABLE `test` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `add_time` datetime NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        */

        parent::setUp(); // TODO: Change the autogenerated stub
        Config::set('db', [
            'default' => [
                'no_cache'=>true,
                'is_dev_mode' => true,
                'conn'        => [
                    'driver'   => 'pdo_mysql',
                    'dbname'   => 'cmm',
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'password' => '123456',
                ],
                'path'        => [],
            ],
            'mall'    => [
                'no_cache'=>true,
                'is_dev_mode' => true,
                'conn'        => [
                    'driver'   => 'pdo_mysql',
                    'dbname'   => 'mysql',
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'password' => '123456',
                ],
                'path'        => [],
            ],
        ]);
    }

    public function test1()
    {
        $obj = new testModel();

        $dbini = $obj::DB_INI;

        $this->assertEquals('mall', $dbini);

        $this->assertEquals(Db::create($dbini)->getEntityManager()->getConnection()->getDatabase(), 'mysql');

        $obj2 = new test2Model();

        $this->assertEquals(Db::create($obj2::DB_INI)->getEntityManager()->getConnection()->getDatabase(), 'cmm');
    }
}

class test2Model extends Model
{
}

class  testModel extends Model
{
    const DB_INI = 'mall';
    private $id;
    private $name;
    private $addTime;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return testModel
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return testModel
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddTime()
    {
        return $this->addTime;
    }

    /**
     * @param mixed $addTime
     *
     * @return testModel
     */
    public function setAddTime($addTime)
    {
        $this->addTime = $addTime;

        return $this;
    }

}