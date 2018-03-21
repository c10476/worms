<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/14
 * Time: 23:59
 */

namespace worms\core;

use Doctrine\DBAL\Logging\SQLLogger;

class SqlLog implements SQLLogger
{
    const MAX_STRING_LENGTH = 32;
    const BINARY_DATA_VALUE = '(binary value)';

    static protected $num = 0;
    protected $start_time;
    protected $sql;

    /**
     * @desc   getCount 获取sql执行数量
     * @author storn
     * @return int
     */
    static public function getCount()
    {
        return self::$num;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        self::$num++;
        $this->start_time = microtime(true);
        $sql              = "[" . self::$num . "] " . $sql;
        if (null !== $params) {
            $paramsArray = $this->normalizeParams($params);
            $paramsArray->count() > 0
            and
            $sql = preg_replace_callback('/\?/', function () use ($paramsArray) {
                $current = $paramsArray->current();
                $paramsArray->next();

                return $current;
            }, $sql);
        }
        $this->sql = $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        Log::sql(sprintf('%s [Exec: %.6f s]', $this->sql, microtime(true) - $this->start_time));
    }

    /**
     * @desc   normalizeParams
     * @author storn
     *
     * @param array $params
     *
     * @return \ArrayIterator
     */
    private function normalizeParams(array $params)
    {
        $data = [];
        foreach ($params as $index => $param) {
            // normalize recursively

            switch (gettype($param)) {
                case 'object':
                    if ($param instanceof \DateTime) {
                        $data[$index] = $param->format('Y-m-d H:i:s');
                    } else {
                        $data[$index] = "[object]";
                    }
                    break;
                case 'NULL':
                    $data[$index] = 'null';
                    break;
                case 'array':
                    $data[$index] = array_map(function ($val){
                        return is_int($val) ? $val : "'{$val}'";
                    },$param);
                    break;
                default:
                    $param = (string)$param;
                    // non utf-8 strings break json encoding
                    if (!preg_match('//u', $params[$index])) {
                        $data[$index] = self::BINARY_DATA_VALUE;
                        continue;
                    }

                    // detect if the too long string must be shorten
                    if (self::MAX_STRING_LENGTH < mb_strlen($params[$index], 'UTF-8')) {
                        $data[$index] = mb_substr($params[$index], 0, self::MAX_STRING_LENGTH - 6, 'UTF-8') . ' [...]';
                        continue;
                    }
                    if (!is_numeric($param)) {
                        $param = "'" . addslashes($param) . "'";
                    }
                    $data[$index] = $param;
            }
        }

        return new \ArrayIterator($data);
    }

}