<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/1
 * Time: 22:45
 */

namespace storn\worms\tests;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use worms\core\App;
use worms\core\Config;
use worms\core\Db;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
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
        ]);
    }

    protected function formatStr(\worms\core\QueryBuilder $builder)
    {
        $str = strtolower($builder);

        $str = preg_replace('/[ ]{2,}/', ' ', $str);

        $str = preg_replace('/[ ]+$/', '', $str);

        return $str;
    }

    public function testSelect()
    {
        $expected = 'select * from `master` where id = ?';
        $actual   = Db::create()->sqlBuilder()
            ->select()
            ->from('master')
            ->where('id')
            ->eq(1);
        $this->assertEquals($expected, $this->formatStr($actual));

        $expected = 'select * from `master` where id in (?,?)';
        $actual   = Db::create()->sqlBuilder()
            ->select()
            ->from('master')
            ->where('id')
            ->in([1, 2]);
        $this->assertEquals($expected, $this->formatStr($actual));

        $this->assertEquals(Db::create()->query($expected, [1, 2])->fetch(), $actual->fetch());

    }

    public function testSelect3()
    {

        $expected = 'select * from master where id like 1';
        $actual   = Db::create()->sqlBuilder()
            ->select()
            ->from('master')
            ->where('id')
            ->exp('1', 'like');

        $this->assertEquals(Db::create()->query($expected)->fetch(), $actual->fetch());

        $expected = 'select * from master where id = groupid';
        $actual   = Db::create()->sqlBuilder()
            ->select()
            ->from('master')
            ->where('id=groupid');

        $this->assertEquals(Db::create()->query($expected)->fetch(), $actual->fetch());
        $actual = Db::create()->sqlBuilder()
            ->select()
            ->from('master')
            ->where('id')
            ->exp('=groupid');
        $this->assertEquals(Db::create()->query($expected)->fetch(), $actual->fetch());

    }

    public function testSelect2()
    {
        $expected = Db::create()->query('select * from master where id = 1')->fetch();

        $actual = Db::create()->sqlBuilder()
            ->select('*')
            ->from('master')
            ->where('id')
            ->eq(1)
            ->fetch();
        $this->assertEquals($expected, $actual);

        $expected = Db::create()->query('select * from master where id = 1 or id = 2')->fetch();

        $actual = Db::create()->sqlBuilder()
            ->select('*')
            ->from('master')
            ->where('id')
            ->eq(1)
            ->orWhere('id=2')
            ->fetch();
        $this->assertEquals($expected, $actual);
    }

    public function testSelectOrder()
    {
        $expected = Db::create()->query('select * from master where id > 1 order by id desc')->fetchAll();

        $actual = Db::create()->sqlBuilder()
            ->select('*')
            ->from('master')
            ->where('id')->gt(1)
            ->order('id desc')
            ->fetchAll();
        $this->assertEquals($expected, $actual);
    }

    public function testUpdate1()
    {
        $now      = date('Y-m-d H:i:s');
        $expected = "update `master` set `add_time`=? where id = ?";
        $actual   = Db::create()->sqlBuilder()
            ->update('master')
            ->set('add_time')
            ->value($now)
            ->where('id')
            ->eq(1);
        $this->assertEquals($expected, $this->formatStr($actual));

        $affectNums = $actual->exec();
        $this->assertEquals($affectNums, 1);

        $arr = Db::create()->sqlBuilder()
            ->select('add_time')
            ->from('master')
            ->where('id=1')
            ->getField('add_time');
        $this->assertEquals($arr, $now);

    }

    public function testInsert()
    {
        $expected = "insert into `test` (`id`,`name`,`add_time`)values(null,?,?)";

        $now    = date('Y-m-d H:i:s');
        $actual = Db::create()
            ->sqlBuilder()
            ->insert('test')
            ->set('id')->expValue('null')
            ->set('name')->value('cmm')
            ->set('add_time')->value($now);
        $this->assertEquals($expected, $this->formatStr($actual));

        $affectNum = $actual->exec();
        $this->assertEquals($affectNum, 1);

        $actual = Db::create()->sqlBuilder()
            ->select('*')
            ->from('test')
            ->where('name')->eq('cmm')
            ->order('id asc')
            ->getField('id');
        $this->assertEquals(1, $actual);

        $expected = "insert into `test` (`name`,`add_time`)values(?,now())";
        $actual   = Db::create()->sqlBuilder()
            ->insert('test')
            ->set('name')->value('cmm2')
            ->set('add_time')->expValue('now()');
        $this->assertEquals($expected, $this->formatStr($actual));
        $maxId = Db::create()->sqlBuilder()
            ->select('max(id) as max')
            ->from('test')
            ->getField('max');
        $actual->exec();
        $this->assertEquals($maxId + 1, Db::create()->getLastInsertId());

    }

    public function testReplace()
    {
        $expected = "replace into `test` (`id`,`name`,`add_time`)values(?,?,now())";
        $actual   = Db::create()->sqlBuilder()
            ->replace('test')
            ->set('id')->value('1')
            ->set('name')->value('cmm2')
            ->set('add_time')->expValue('now()');
        $this->assertEquals($expected, $this->formatStr($actual));
        $num = $actual->exec();
        $this->assertEquals($num, 2);
        $name = Db::create()->sqlBuilder()
            ->select('name')
            ->from('test')
            ->where('id')->eq(1)
            ->getField('name');
        $this->assertEquals($name, 'cmm2');
    }

    public function testDelete()
    {
        $expected = 'delete from `test` where id = ?';
        $actual   = Db::create()->sqlBuilder()
            ->delete('test')
            ->where('id')->eq(12);
        $this->assertEquals($expected, $this->formatStr($actual));

        Db::create()->sqlBuilder()
            ->replace('test')
            ->set('id')->value('12')
            ->set('name')->value('test')
            ->set('add_time')->expValue('now()')
            ->exec();
        $num = $actual->exec();
        $this->assertEquals($num, 1);
    }

    public function testJoin()
    {
        $actual = Db::create()->sqlBuilder()
            ->select('*')
            ->from('master', 'm')
            ->join('test', 't')
            ->on('m.id', 't.id');

        $this->assertEquals($actual->fetchAll(), Db::create()->query('select * from master m inner join test t ON m.id = t.id')->fetchAll());
        $actual = Db::create()->sqlBuilder()
            ->select('*')
            ->from('master', 'm')
            ->join('test', 't')
            ->on('m.id', 't.id')
            ->on('m.id', 't.id+1', '=', 'or');

        $this->assertEquals($actual->fetchAll(), Db::create()->query('select * from `master` m inner join test t ON m.id = t.id or m.id=t.id+1')->fetchAll());
    }

    public function testLeftJoin()
    {
        $actual = Db::create()->sqlBuilder()
            ->select('*')
            ->from('master', 'm')
            ->join('test', 't')
            ->on('m.id', 't.id')
            ->leftJoin('test', 'tt')
            ->on('m.id', 'tt.id+1');

        echo $this->formatStr($actual);
    }

    public function testLike()
    {
        $query = Db::create()->sqlBuilder()
            ->select()
            ->from('master')
            ->where('id')->eq('1')
            ->where('name')->like('123')
            ->where('id2')->match('344_%');

        $this->assertEquals($query->getSql(), 'SELECT * FROM `master`  WHERE  id = ?  AND  name like ?  AND  id2 like ? ');

    }

    public function testThreeJoin()
    {
        $query = Db::create()->sqlBuilder()
            ->select()
            ->from('a')
            ->leftJoin('b')->on('a.id', 'b.id')
            ->leftJoin('c')->on('a.id', 'c.id')->on('a.bid','c.bid','!=','or');

        echo $query->getSql();

    }
}