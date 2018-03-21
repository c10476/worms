<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/4
 * Time: 13:58
 */

namespace worms\tool;

use worms\core\AppException;
use worms\core\Db;

class ModelXmlBuilder
{

    /** @var  Db */
    private $db;
    private $tableName;
    private $xml;

    protected $entity;
    protected $namespace = '';

    static private $tpl = [
        'string' => '<field name="%s" type="string" column="%s" %s nullable="%s">
            <options>
                <option name="fixed"/>
                <option name="comment">%s</option>
            </options>
        </field>',
    ];

    /**
     * @desc   setTableName 设置表名
     * @author storn
     *
     * @param $tableName
     *
     * @return $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @desc   setDb 设置db
     * @author storn
     *
     * @param Db $db
     *
     * @return $this
     */
    public function setDb(Db $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        if (is_null($this->entity)) {
            $this->entity = ucfirst($this->underline2Camel($this->tableName));
            if ($this->namespace) {
                $this->entity = $this->namespace . '\\' . $this->entity;
            }
        }

        return $this->entity;
    }

    /**
     * @param mixed $entity
     *
     * @return ModelXmlBuilder
     */
    public function setEntity($entity)
    {
        $this->entity = $this->namespace . '\\' . ucfirst($this->underline2Camel($entity));

        return $this;
    }

    public function builder($path = '.')
    {
        $this->parse();
        $filepath = $path . '/' . str_replace('\\', '.', $this->getEntity()) . '.dcm.xml';
        if (!file_put_contents($filepath, $this->xml)) {
            throw new AppException('xml生成失败' . $filepath, 'errr');
        }
        echo ($filepath . '生成成功') . PHP_EOL;
    }

    protected function parse()
    {
        $tableInfo = $this->db->query('show full columns from ' . $this->tableName)->fetchAll(\PDO::FETCH_NUM);
        $this->xml = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
        $this->xml .= '<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">' . PHP_EOL;
        $this->xml .= "<entity name=\"{$this->getEntity()}\" table=\"{$this->tableName}\">\n";
        $hasId = false;
        foreach ($tableInfo as $filed) {
            $this->xml .= $this->parseField($filed);
            if ($filed[0] == 'id') {
                $hasId = true;
            }
        }
        if (!$hasId) {
            throw new AppException($this->tableName . '该表没有主键，创建失败');
        }
        $this->xml .= "\t\t</entity>\n</doctrine-mapping>\n";
    }

    protected function parseField($fieldInfo)
    {
        list($field, $type, $collation, $null, $key, $default, $extra, $p, $comment) = $fieldInfo;
        if ($key == 'PRI') {
            return "\t<id name=\"id\" type=\"integer\" column=\"id\">\n\t\t<generator strategy=\"IDENTITY\"/>\n\t</id>\n";
        }
        $str = "\t<field ";
        $str .= sprintf('column="%s" ', $field);
        $str .= sprintf('name="%s" ', $this->underline2Camel($field));
        $str .= $this->getType($type);
        $str .= sprintf('nullable ="%s" ', $null == "NO" ? 'false' : 'true');
        $str .= ">\n";
        $str .= "\t\t<options>\n";
        $comment && $str .= "\t\t\t<option name=\"comment\">{$comment}</option>\n ";
        if ($default == 'CURRENT_TIMESTAMP') {
            $default = null;
        }
        if (!is_null($default)) {
            if ($default !== '') {
                $str .= "\t\t\t<option name=\"default\">{$default}</option>\n";
            } else {
                $str .= "\t\t\t<option name=\"default\"/>\n";
            }
        }
        if (strpos($type, 'unsigned') !== false) {
            $str .= "\t\t\t <option name=\"unsigned\"/>\n";
        }
        $str .= "\t\t</options>\n\t</field>\n";

        return $str;
    }

    public function underline2Camel($str)
    {
        return lcfirst(str_replace('_', '', ucwords($str, '_')));
    }

    protected function getType($type)
    {
        if (strpos($type, 'int') !== false) {
            return 'type="integer" ';
        } elseif (strpos($type, 'char') !== false || strpos($type, 'enum') !== false) {
            preg_match('/[0-9]+/', $type, $match);
            $num = $match[0];

            return 'type="string" length="' . $num . '" ';

        } elseif (in_array($type, ['date', 'datetime', 'time', 'timestamp'])) {

            return 'type="datetime" ';
        } elseif (strpos($type, 'decimal') !== false || strpos($type, 'float') !== false) {
            preg_match('/\(([0-9]+?),([0-9]+)\)/', $type, $match);
            $scale     = $match[0][1];
            $precision = $match[0][2];

            return "type=\"decimal\" scale=\"{$scale}\" precision=\"{$precision}\" ";
        } elseif (in_array($type, ['text','longtext'])) {
            return 'type="string" length="65535" ';
        } else {
            throw new AppException('不支持的字段类型' . $type);
        }
    }
}