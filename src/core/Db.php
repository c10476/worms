<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/13
 * Time: 14:47
 */

namespace worms\core;

use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use worms\cache\driver\Memcached;
use worms\cache\driver\Redis;
use worms\model\InvalidFieldValue;
use worms\model\MXmlDriver;

class Db
{
    /** @var  Db[] $instances */
    static private $instances;

    private $options = [];
    //该数据库配置名称 唯一
    private $db_name;

    /** @var  EntityManager */
    private $entityManager;

    /**
     * Db constructor.
     *
     * @param array $options 数据库配置
     *
     * @throws AppException
     */
    public function __construct($options)
    {
        $this->options = $options;
        $this->db_name = isset($options['name']) ? $options['name'] : md5(serialize($options));

        $dbCache = null;
        if (isset($this->options['cache'])) {
            $cache_options = $this->options['cache'];
            switch (strtolower($cache_options['type'])) {
                case 'redis':
                    $dbCache = new RedisCache();
                    $dbCache->setRedis((new Redis($cache_options))->handler());

                    break;
                case 'memcached':
                    $dbCache = new MemcachedCache($cache_options);
                    $dbCache->setMemcached((new Memcached($cache_options))->handler());
                    break;
                default:
                    throw new AppException("only support memcached|redis");
            }
            $dbCache->setNamespace('db_' . $cache_options['namespace']);
        }

        $config = Setup::createConfiguration($this->options['is_dev_mode'] == true, null);
        if ($dbCache) {
            $config->setMetadataCacheImpl($dbCache);
            $config->setQueryCacheImpl($dbCache);
            $config->setResultCacheImpl($dbCache);
        }
        array_push($this->options['path'], dirname(__DIR__) . '/model/xml');

        $config->setMetadataDriverImpl(new MXmlDriver($this->options['path']));
        $config->setSQLLogger(new SqlLog());
        try {
            $this->entityManager = EntityManager::create($this->options['conn'], $config);

        } catch (\Exception $e) {
            throw new AppException("创建DB实例失败，请检查实例", 'DB_CREATE_FAILED', $this->options);
        }
        self::$instances[$this->db_name] = $this;
    }

    /**
     * @desc   create
     * @author storn
     *
     * @param string $name db配置名称
     *
     * @return Db
     * @throws AppException
     */
    static public function create($name = 'default')
    {
        if (!isset(self::$instances[$name])) {
            $conf          = Config::get('db.' . $name);
            $conf['name']  = $name;
            $conf['cache'] = Config::get('db_cache');
            new self($conf);
        }

        return self::$instances[$name];
    }

    /**
     * @desc   save 更新或者插入
     * @author storn
     *
     * @param object $object 待更新或者插入的对象 entity
     *
     * @throws AppException
     * @return Db
     */
    public function save($object)
    {
        $this->entityManager->persist($object);

        return $this;
    }

    /**
     * @desc   remove
     * @author storn
     *
     * @param $object
     *
     * @throws AppException
     * @return Db
     */
    public function remove($object)
    {
        $this->entityManager->remove($object);

        return $this;
    }

    /**
     * @desc   flush
     * @author storn
     * @throws AppException
     */
    public function flush()
    {
        try {
            $this->entityManager->flush();
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AppException($e->getMessage(), $e->getCode(), $e->getTrace());
        }
    }

    /**
     * @desc   allFlush 所有db 执行更新操作
     * @author storn
     */
    static public function allFlush()
    {
        foreach (self::$instances as $instance) {
            $instance->flush();
        }
    }

    /**
     * @desc   qb
     * @author storn
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function dqlBuilder()
    {
        return $this->entityManager->createQueryBuilder();
    }

    /**
     * @desc   sqlBuilder
     * @author storn
     * @return QueryBuilder
     */
    public function sqlBuilder()
    {
        return new QueryBuilder($this, $this->options['queryBuilder']);
    }

    /**
     * Finds an Entity by its identifier.
     *
     * @param string       $entityName  The class name of the entity to find.
     * @param mixed        $id          The identity of the entity to find.
     * @param integer|null $lockMode    One of the \Doctrine\DBAL\LockMode::* constants
     *                                  or NULL if no specific lock mode should be used
     *                                  during the search.
     * @param integer|null $lockVersion The version of the entity to find when using
     *                                  optimistic locking.
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     *
     */
    public function find($entityName, $id, $lockMode = null, $lockVersion = null)
    {
        return $this->entityManager->find($entityName, $id, $lockMode, $lockVersion);
    }

    /**
     * @desc   exec
     * @author storn
     *
     * @param string $sql    sql
     * @param array  $params 绑定参数列表
     *
     * @return int
     */
    public function exec($sql, $params = [])
    {
        return $this->entityManager->getConnection()->executeUpdate($sql, $params);
    }

    /**
     * @desc   fetch
     * @author storn
     *
     * @param string $sql    sql
     *
     * @param array  $params 参数列表
     *
     * @return \Doctrine\DBAL\Driver\Statement The executed statement.
     */
    public function query($sql, $params = [])
    {
        return $this->entityManager->getConnection()->executeQuery($sql, $params);
    }

    /**
     * @desc   getLastInsertId 上一次插入的id
     * @author storn
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->entityManager->getConnection()->lastInsertId();
    }

    /**
     * @desc   getEntityManager
     * @author storn
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @desc   transactional
     * @author storn
     *
     * @param \Closure $callback
     */
    public function transactional(\Closure $callback)
    {
        $closure = \Closure::bind(function () use ($callback) {
            $callback();
            $this->flush();
        }, $this);

        $this->entityManager->getConnection()->transactional($closure);
    }
}