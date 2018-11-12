<?php
/**
 * @date        2017-12-14
 * @author      qingbing<780042175@qq.com>
 * @version     1.0
 */

namespace pf\db;

use pf\core\Core;

class TableSchema extends Core
{
    /**
     * db-table-name（不带 引用）
     * @var string
     */
    public $name;
    /**
     * db-table-name（带引用，有前缀）
     * @var string
     */
    public $rawName;
    /**
     * 数据表的主键，如果为复合主键，将返回一个数组
     * @var string|array
     */
    public $primaryKey;
    /**
     * Sequence name for the primary key.
     * @var string
     */
    public $sequenceName;
    /**
     * 表列的元数据
     * @var array
     */
    public $columns = [];

    /**
     * 获取列元素
     * @param string $name
     * @return ColumnSchema metadata of the named column
     */
    public function getColumn($name)
    {
        return isset($this->columns[$name]) ? $this->columns[$name] : null;
    }

    /**
     * 返回所有列名
     * @return array
     */
    public function getColumnNames()
    {
        return array_keys($this->columns);
    }
}

class ColumnSchema extends Core
{
    /**
     * 列名（不带引用）
     * @var string
     */
    public $name;
    /**
     * 列名（带引用）
     * @var string
     */
    public $rawName;
    /**
     * 是否允许为空
     * @var bool
     */
    public $allowNull;
    /**
     * 数据表设计类型
     * @var string
     */
    public $dbType;
    /**
     * 字段对应的 PHP 类型
     * @var string
     */
    public $type;
    /**
     * 字段默认值
     * @var mixed
     */
    public $defaultValue;
    /**
     * 字段的长短
     * @var int
     */
    public $size;
    /**
     * 数字类型时，数字类型的精度
     * @var int
     */
    public $precision;
    /**
     * 数字类型时,标度
     * @var int
     */
    public $scale;
    /**
     * 该字段是否为主键
     * @var bool
     */
    public $isPrimaryKey;
    /**
     * 该字段是否为外键
     * @var bool
     */
    public $isForeignKey;
    /**
     * 该字段是否为自增
     * @var bool
     */
    public $autoIncrement = false;
    /**
     * 该字段的备注信息
     * @var string
     */
    public $comment = '';

    /**
     * 使用数据库类型和默认值初始化列
     * 设置列的PHP类型，大小，精度，缩放，默认值
     * @param string $dbType
     * @param mixed $defaultValue
     */
    public function init($dbType, $defaultValue)
    {
        $this->dbType = $dbType;
        $this->extractType($dbType);
        $this->extractLimit($dbType);
        if (null !== $defaultValue) {
            $this->extractDefault($defaultValue);
        }
    }

    /**
     * 将 db-type 转换成 php-type
     * @param string $dbType
     */
    protected function extractType($dbType)
    {
        if (false !== stripos($dbType, 'int') && false === stripos($dbType, 'unsigned int')) {
            $this->type = 'integer';
        } else if (false !== stripos($dbType, 'bool')) {
            $this->type = 'boolean';
        } else if (preg_match('/(real|floa|doub)/i', $dbType)) {
            $this->type = 'double';
        } else {
            $this->type = 'string';
        }
    }

    /**
     * 从列的数据库类型中提取大小、精度、标度信息
     * @param string $dbType
     */
    protected function extractLimit($dbType)
    {
        if (strpos($dbType, '(') && preg_match('/\((.*)\)/', $dbType, $matches)) {
            $values = explode(',', $matches[1]);
            $this->size = $this->precision = (int)$values[0];
            if (isset($values[1])) {
                $this->scale = (int)$values[1];
            }
        }
    }

    /**
     * 提取列的默认值
     * @param mixed $defaultValue
     */
    protected function extractDefault($defaultValue)
    {
        $this->defaultValue = $this->typecast($defaultValue);
    }

    /**
     * 将 php值 转换或 db-type
     * @param mixed $value
     * @return mixed converted value
     */
    public function typecast($value)
    {
        if (gettype($value) === $this->type || null === $value) {
            return $value;
        }
        if ('' === $value && $this->allowNull) {
            return $this->type === 'string' ? '' : null;
        }
        switch ($this->type) {
            case 'string':
                return (string)$value;
            case 'integer':
                return (integer)$value;
            case 'boolean':
                return (boolean)$value;
            case 'double':
            default:
                return $value;
        }
    }
}