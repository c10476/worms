<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/1
 * Time: 20:57
 */

namespace worms\core;

class QueryBuilder
{
    const TYPE_SELECT = 'SELECT';
    const TYPE_UPDATE = 'UPDATE';
    const TYPE_INSERT = 'INSERT';
    const TYPE_REPLACE = 'REPLACE';
    const TYPE_DELETE = 'DELETE';

    const QUERY_WHERE = 'where';
    const QUERY_DATA = 'data';

    const LOGIC_AND = 'AND';
    const LOGIC_OR = 'OR';

    private $options = [
        'tablePrefix' => '',//表名前缀
    ];

    private $query_type = self::TYPE_SELECT;//sql类型
    private $current_field;//当前字段
    private $current_logic;//and or
    private $current_type;//where 还是 data
    private $current_value;//值
    private $current_exp;//表达式
    private $join = '';//关联表

    private $table_name;//表名
    private $data;
    private $field = '*';

    private $joinTable;//关联表
    private $joinCondition;//关联条件
    private $joinType;//json方式

    /**
     * field:'id'
     * exp:'='
     * value:'2'
     *
     * @var
     */
    private $where;
    private $order;
    private $limit;
    private $params = [];
    private $sql;
    /** @var  Db */
    private $db;

    /**
     * QueryBuilder constructor.
     *
     * @param Db $db
     * @param    $options
     */
    public function __construct(Db $db, $options)
    {
        $this->db      = $db;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @desc   setOptions 配置
     * @author storn
     *
     * @param string $option 配置项
     * @param mixed  $value  配置值
     *
     * @return QueryBuilder
     */
    public function setOptions($option, $value): QueryBuilder
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * @desc   select
     * @author storn
     *
     * @param string $field 要查询的表达式
     *
     * @return QueryBuilder
     */
    public function select($field = '*'): QueryBuilder
    {
        $this->query_type = self::TYPE_SELECT;
        $this->field      = $field;

        return $this;
    }

    /**
     * @desc   from 要查询的表
     * @author storn
     *
     * @param string $tableName 表名称
     * @param string $alias     别名
     *
     * @return QueryBuilder
     */
    public function from($tableName, $alias = ''): QueryBuilder
    {
        $this->setTableName($tableName, $alias);

        return $this;
    }

    /**
     * @desc   join
     * @author storn
     *
     * @param        $tableName
     * @param string $alias
     * @param string $type
     *
     * @return QueryBuilder
     */
    public function join($tableName, $alias = '', $type = 'INNER'): QueryBuilder
    {
        $this->parseJoin();
        $this->joinType  = $type;
        $this->joinTable = "`$tableName`";
        if ($alias) {
            $this->joinTable .= " " . trim($alias);
        }

        return $this;
    }

    /**
     * @desc   leftJoin
     * @author storn
     *
     * @param        $tableName
     * @param string $alias
     *
     * @return QueryBuilder
     */
    public function leftJoin($tableName, $alias = ''): QueryBuilder
    {
        return $this->join($tableName, $alias, 'LEFT');
    }

    /**
     * @desc   rightJoin
     * @author storn
     *
     * @param        $tableName
     * @param string $alias
     *
     * @return QueryBuilder
     */
    public function rightJoin($tableName, $alias = ''): QueryBuilder
    {
        return $this->join($tableName, $alias, 'RIGHT');
    }

    /**
     * @desc   on
     * @author storn
     *
     * @param        $fieldA
     * @param        $fieldB
     * @param string $exp
     * @param string $logic
     *
     * @return QueryBuilder
     */
    public function on($fieldA, $fieldB, $exp = '=', $logic = 'and'): QueryBuilder
    {
        $this->joinCondition[] = [$fieldA, $fieldB, $exp, $logic];

        return $this;
    }

    /**
     * @desc   parseJoin 解析关联表关系
     * @author storn
     */
    protected function parseJoin()
    {
        if ($this->joinTable && $this->joinType && $this->joinCondition) {
            $sql   = "$this->joinType JOIN {$this->joinTable}";
            $index = 0;
            foreach ($this->joinCondition as $condition) {
                list($filedA, $filedB, $exp, $logic) = $condition;
                if ($index++ === 0) {
                    $sql .= " ON {$filedA} {$exp} {$filedB}";
                } else {
                    $sql .= " {$logic} {$filedA} {$exp} {$filedB}";
                }
            }
            $this->joinTable = $this->joinType = $this->joinCondition = null;

            if ($this->join) {
                $this->join .= ' ';
            }
            $this->join .= $sql;
        }
    }

    /**
     * @desc   update 更新语句
     * @author storn
     *
     * @param string $tableName 表名称
     *
     * @return $this
     */
    public function update($tableName): QueryBuilder
    {
        $this->query_type = self::TYPE_UPDATE;
        $this->setTableName($tableName);

        return $this;
    }

    /**
     * @desc   insert
     * @author storn
     *
     * @param string $tableName 表名称
     *
     * @return $this
     */
    public function insert($tableName): QueryBuilder
    {
        $this->query_type = self::TYPE_INSERT;
        $this->setTableName($tableName);

        return $this;
    }

    /**
     * @desc   replace replace语句
     * @author storn
     *
     * @param string $tableName 表名称
     *
     * @return $this
     */
    public function replace($tableName): QueryBuilder
    {
        $this->query_type = self::TYPE_REPLACE;
        $this->setTableName($tableName);

        return $this;
    }

    /**
     * @desc   delete 删除
     * @author storn
     *
     * @param string $tableName 表名
     *
     * @return $this
     */
    public function delete($tableName): QueryBuilder
    {
        $this->query_type = self::TYPE_DELETE;
        $this->setTableName($tableName);

        return $this;
    }

    /**
     * @desc   where 等价于andWhere
     * @author storn
     *
     * @param string $field 字段或者表达式
     *
     * @return QueryBuilder
     */
    public function where($field): QueryBuilder
    {
        return $this->andWhere($field);
    }

    /**
     * @desc   andWhere 与条件语句
     * @author storn
     *
     * @param string $field 字段或者表达式
     *
     * @return $this
     */
    public function andWhere($field): QueryBuilder
    {
        $this->parseCurrentField();
        $this->current_type  = self::QUERY_WHERE;
        $this->current_logic = self::LOGIC_AND;
        $this->current_field = $field;

        return $this;
    }

    /**
     * @desc   orWhere 或者条件
     * @author storn
     *
     * @param string $field 字段或者表达式
     *
     * @return $this
     */
    public function orWhere($field): QueryBuilder
    {
        $this->parseCurrentField();
        $this->current_type  = self::QUERY_WHERE;
        $this->current_logic = self::LOGIC_OR;
        $this->current_field = $field;

        return $this;
    }

    /**
     * @desc   eq 等于
     * @author storn
     *
     * @param string $value 值
     *
     * @return QueryBuilder
     */
    public function eq($value): QueryBuilder
    {
        return $this->exp($value, '=');
    }

    /**
     * @desc   gt 大于某个值
     * @author storn
     *
     * @param string $value 值
     *
     * @return QueryBuilder
     */
    public function gt($value): QueryBuilder
    {
        return $this->exp($value, '>');
    }

    /**
     * @desc   lt 小于
     * @author storn
     *
     * @param string $value 值
     *
     * @return QueryBuilder
     */
    public function lt($value): QueryBuilder
    {
        return $this->exp($value, '<');
    }

    /**
     * @desc   neq 不等于
     * @author storn
     *
     * @param string $value 值
     *
     * @return QueryBuilder
     */
    public function neq($value): QueryBuilder
    {
        return $this->exp($value, '!=');
    }

    /**
     * @desc   ge 大于等于
     * @author storn
     *
     * @param string $value 值
     *
     * @return QueryBuilder
     */
    public function ge($value): QueryBuilder
    {
        return $this->exp($value, '>=');
    }

    /**
     * @desc   le 小于等于
     * @author storn
     *
     * @param string $value 值
     *
     * @return QueryBuilder
     */
    public function le($value): QueryBuilder
    {
        return $this->exp($value, '<=');
    }

    /**
     * @desc   in
     * @author storn
     *
     * @param array $array 查询的范围
     *
     * @return QueryBuilder
     */
    public function in(array $array)
    {
        return $this->exp($array, 'IN');
    }

    /**
     * @desc   notin
     * @author storn
     *
     * @param array $array notin的范围
     *
     * @return QueryBuilder
     */
    public function notin(array $array): QueryBuilder
    {
        return $this->exp($array, 'NOT IN');
    }

    /**
     * @desc   match 模糊匹配关键字
     * @author storn
     *
     * @param string $keywords 关键字
     *
     * @return QueryBuilder
     */
    public function match($keywords): QueryBuilder
    {
        $kw = str_replace("_", "\_", trim($keywords));
        $kw = str_replace("%", "\%", $kw);

        return $this->like("%{$kw}%");
    }

    /**
     * @desc   like
     * @author storn
     *
     * @param string $keywords 关键字
     *
     * @return QueryBuilder
     */
    public function like($keywords): QueryBuilder
    {
        return $this->exp($keywords, 'like');
    }

    /**
     * @desc   isNull
     * @author storn
     * @return QueryBuilder
     */
    public function isNull(): QueryBuilder
    {
        $this->current_field .= ' IS NULL ';

        return $this;
    }

    /**
     * @desc   set update 和 insert 设置字段的值
     * @author storn
     *
     * @param string $field 字段名称
     *
     * @return QueryBuilder
     */
    public function set($field): QueryBuilder
    {
        $this->parseCurrentField();
        $this->current_logic = self::LOGIC_AND;
        $this->current_field = $field;
        $this->current_type  = self::QUERY_DATA;

        return $this;
    }

    /**
     * @desc   value 值
     * @author storn
     *
     * @param string $value 设置的值
     *
     * @return QueryBuilder
     */
    public function value($value): QueryBuilder
    {
        $this->exp($value, false);

        return $this;
    }

    /**
     * @desc   value 表达式值
     * @author storn
     *
     * @param string $value 设置的值 表达式值
     *
     * @return QueryBuilder
     */
    public function expValue($value): QueryBuilder
    {
        $this->exp($value, true);

        return $this;
    }

    /**
     * @desc   isNotNull
     * @author storn
     * @return QueryBuilder
     */
    public function isNotNull(): QueryBuilder
    {
        $this->current_field .= ' IS NOT NULL ';

        return $this;
    }

    /**
     * @desc   exp
     * @author storn
     *
     * @param      $value
     * @param null $exp
     *
     * @return QueryBuilder
     */
    public function exp($value, $exp = null): QueryBuilder
    {
        $this->current_exp   = $exp;
        $this->current_value = $value;
        $this->parseCurrentField();

        return $this;
    }

    /**
     * @desc   order 排序
     * @author storn
     *
     * @param string $order 排序
     *
     * @return QueryBuilder
     */
    public function order($order): QueryBuilder
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @desc   limit
     * @author storn
     *
     * @param int $start 开始
     * @param int $size  数量
     *
     * @return QueryBuilder
     */
    public function limit($start, $size): QueryBuilder
    {
        $this->limit = " LIMIT {$start},{$size} ";

        return $this;
    }

    /**
     * @desc   setTableName 设置表名
     * @author storn
     *
     * @param string $tableName 表名称
     * @param string $alias     表别名
     */
    protected function setTableName($tableName, $alias = '')
    {
        $this->table_name = "`{$this->options['tablePrefix']}{$tableName}`";
        if ($alias) {
            $this->table_name .= " AS {$alias} ";
        }
    }

    /**
     * @desc   __toString
     * @author storn
     * @return string
     */
    public function __toString()
    {
        return $this->getSql();
    }

    /**
     * @desc   getSql 获取生成的sql
     * @author storn
     * @return string
     */
    public function getSql(): string
    {
        if (is_null($this->sql)) {
            $this->parse();
        }

        return $this->sql;
    }

    /**
     * @desc   parse 解析sql
     * @author storn
     * @return QueryBuilder
     */
    public function parse(): QueryBuilder
    {
        $this->parseCurrentField();
        $this->sql = '';
        switch ($this->query_type) {
            case self::TYPE_SELECT:
                $this->parseSelect();
                break;
            case self::TYPE_UPDATE:
                $this->parseUpdate();
                break;
            case self::TYPE_INSERT:
                $this->parseInsert();
                break;
            case self::TYPE_REPLACE:
                $this->parseReplace();
                break;
            case self::TYPE_DELETE:
                $this->parseDelete();
                break;
        }

        return $this;
    }

    /**
     * @desc   parseDelete
     * @author storn
     */
    public function parseDelete()
    {
        $this->sql = "DELETE FROM {$this->table_name} ";
        $this->sql .= $this->getWhereStr();
    }

    /**
     * @desc   parseInsert 解析insert语法
     * @author storn
     */
    protected function parseInsert()
    {
        $this->sql = "INSERT INTO {$this->table_name} ";
        $this->sql .= $this->getInsertStr();
    }

    /**
     * @desc   parseReplace 解析replace语法
     * @author storn
     */
    protected function parseReplace()
    {
        $this->sql = "REPLACE INTO {$this->table_name} ";
        $this->sql .= $this->getInsertStr();
    }

    /**
     * @desc   parseUpdate
     * @author storn
     */
    protected function parseUpdate()
    {
        $this->sql = "UPDATE {$this->table_name}";
        $this->sql .= $this->getUpdateStr();
        $this->sql .= $this->getWhereStr();
    }

    /**
     * @desc   parseSelect 解析select语句
     * @author storn
     */
    protected function parseSelect()
    {
        $this->sql = "SELECT {$this->field} FROM {$this->table_name} ";
        $this->sql .= $this->getJoinStr();
        $this->sql .= $this->getWhereStr();
        $this->order && $this->sql .= " ORDER BY {$this->order} ";
        $this->limit && $this->sql .= " {$this->limit} ";
    }

    /**
     * @desc   getJoinStr
     * @author storn
     * @return string
     */
    protected function getJoinStr()
    {
        $this->parseJoin();

        return $this->join;
    }

    /**
     * @desc   getInsertStr 获取insert字符串
     * @author storn
     * @return string
     */
    protected function getInsertStr()
    {
        $fileds = [];
        $values = [];
        if ($this->data) {
            foreach ($this->data as $item) {
                list(, $field, $exp, $value) = $item;
                $fileds[] = "`$field`";
                if ($exp) {
                    //表达式
                    $values[] = $value;
                } else {
                    $values[]       = '?';
                    $this->params[] = $value;
                }

            }
        }

        return sprintf('(%s)VALUES(%s)', implode(',', $fileds), implode(',', $values));
    }

    /**
     * @desc   getUpdateStr
     * @author storn
     * @return string
     */
    protected function getUpdateStr()
    {
        $str = '';
        if ($this->data) {
            foreach ($this->data as $item) {
                list(, $field, $exp, $value) = $item;
                if ($str == '') {
                    $str = ' SET ';
                } else {
                    $str .= ',';
                }
                if ($exp) {
                    //表达式
                    $str .= "`{$field}`={$value}";
                } else {
                    $str .= "`{$field}`=?";
                    $this->params[] = $value;
                }

            }
        }

        return $str;
    }

    /**
     * @desc   getWhereStr 解析where str
     * @author storn
     * @return string
     */
    protected function getWhereStr()
    {
        $str = '';
        if ($this->where) {
            foreach ($this->where as $item) {
                list($logic, $field, $exp, $value) = $item;
                if ($str == '') {
                    $str .= ' WHERE ';
                } else {
                    $str .= " {$logic} ";
                }

                if ($exp) {
                    if ($exp == 'IN' || $exp == "NOT IN") {
                        if (is_array($value)) {
                            $this->params = array_merge($this->params, $value);
                            $str .= " {$field} {$exp} (" . implode(',', array_fill(0, count($value), '?')) . ") ";
                        }
                        //非数组直接跳过
                    } else {
                        $str .= " {$field} {$exp} ? ";
                        $this->params[] = $value;
                    }
                } else {
                    //运算表达式
                    $str .= " {$field} {$value}";
                }

            }
        }

        return $str;
    }

    /**
     * @desc   parseCurrentField 解析当前字段
     * @author storn
     */
    protected function parseCurrentField()
    {
        if ($this->current_field) {
            $tmp = [
                $this->current_logic,
                $this->current_field,
                $this->current_exp,
                $this->current_value,
            ];
            if ($this->current_type == self::QUERY_WHERE) {
                $this->where[] = $tmp;
            } else {
                $this->data[] = $tmp;
            }
            $this->current_logic = $this->current_exp = $this->current_value = $this->current_field = null;
        }
    }

    /**
     * @desc   fetch
     * @author storn
     * @return mixed
     */
    public function fetch()
    {
        $this->limit(0, 1);

        return $this->db->query($this->getSql(), $this->params)->fetch();
    }

    /**
     * @desc   getFiled
     * @author storn
     *
     * @param string $key 字段名称
     *
     * @return null|string
     */
    public function getField($key)
    {
        $arr = $this->fetch();

        return isset($arr[$key]) ? $arr[$key] : null;
    }

    /**
     * @desc   fetchAll
     * @author storn
     * @return array
     */
    public function fetchAll()
    {
        return $this->db->query($this->getSql(), $this->params)->fetchAll();
    }

    /**
     * @desc   exec return affected rows
     * @author storn
     * @return int
     */
    public function exec()
    {
        return $this->db->exec($this->getSql(), $this->params);
    }

    /**
     * @desc   getParams
     * @author storn
     * @return array
     */
    public function getParams()
    {
        $this->getSql();

        return $this->params;
    }

    /**
     * @desc   getDb 获取db实例
     * @author storn
     * @return Db
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @desc   getLastInsertId
     * @author storn
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->db->getLastInsertId();
    }

    /**
     * clone
     *
     * @author storn
     * @return QueryBuilder
     */
    public function clone(): QueryBuilder
    {
        return clone $this;
    }
}