<?php
/**
 * @date        2017-12-14
 * @author      qingbing<780042175@qq.com>
 * @version     1.0
 */

namespace pf\db\mysql;

class TableSchema extends \pf\db\TableSchema
{
    /**
     * database 的 schema 名称
     * @var string
     */
    public $schemaName;
}

class ColumnSchema extends \pf\db\ColumnSchema
{
    /**
     * 将 db-type 转换成 php-type
     * @param string $dbType
     */
    protected function extractType($dbType)
    {
        if (0 === strncmp($dbType, 'enum', 4)) {
            $this->type = 'string';
        } else if (false !== strpos($dbType, 'float') || false !== strpos($dbType, 'double')) {
            $this->type = 'double';
        } else if (false !== strpos($dbType, 'bool')) {
            $this->type = 'boolean';
        } else if (0 === strpos($dbType, 'int') && false === strpos($dbType, 'unsigned') || preg_match('/(bit|tinyint|smallint|mediumint)/', $dbType)) {
            $this->type = 'integer';
        } else {
            $this->type = 'string';
        }
    }

    /**
     * 提取列的默认值
     * @param mixed $defaultValue
     */
    protected function extractDefault($defaultValue)
    {
        if (0 === strncmp($this->dbType, 'bit', 3)) {
            $this->defaultValue = bindec(trim($defaultValue, 'b\''));
        } else if ('timestamp' === $this->dbType && 'CURRENT_TIMESTAMP' === $defaultValue) {
            $this->defaultValue = null;
        } else {
            parent::extractDefault($defaultValue);
        }
    }

    /**
     * 从列的数据库类型中提取大小、精度、标度信息
     * @param string $dbType
     */
    protected function extractLimit($dbType)
    {
        if (0 === strncmp($dbType, 'enum', 4) && preg_match('/\(([\'"])(.*)\\1\)/', $dbType, $matches)) {
            // explode by (single or double) quote and comma (ENUM values may contain commas)
            $values = explode($matches[1] . ',' . $matches[1], $matches[2]);
            $size = 0;
            foreach ($values as $value) {
                if (($n = strlen($value)) > $size) {
                    $size = $n;
                }
            }
            $this->size = $this->precision = $size;
        } else {
            parent::extractLimit($dbType);
        }
    }
}