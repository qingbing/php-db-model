<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-13
 * Version      :   1.0
 */

namespace DbModel;

use Abstracts\Base;
use Db\Exception;
use DbModel;

class DbMetaData extends Base
{
    /* @var string 模型类名 */
    private $_modelClassName;
    /* @var \Db\TableSchema */
    public $tableSchema;
    /* @var \Db\ColumnSchema[] */
    public $columns;
    /* @var array 属性默认值 */
    public $attributeDefaults = [];
    /* @var \DbModel\Abstracts\DbRelation[] 外部模型关联关系 */
    public $relations = [];

    /**
     * 构造函数
     * @param DbModel $model
     * @throws \Exception
     */
    public function __construct(DbModel $model)
    {
        $this->_modelClassName = get_class($model);
        $tableName = $model->tableName();
        $table = $model->getConnection()->getTable($tableName);
        if (!$table) {
            throw new Exception(str_cover('模型"{class}"的数据表"{tableName}"不存在', [
                '{class}' => $this->_modelClassName,
                '{table}' => $tableName,
            ]), 101500201);
        }
        if (null === $table->primaryKey) {
            $table->primaryKey = $model->primaryKey();
            if (is_string($table->primaryKey) && isset($table->columns[$table->primaryKey])) {
                $table->columns[$table->primaryKey]->isPrimaryKey = true;
            } else if (is_array($table->primaryKey)) {
                foreach ($table->primaryKey as $name) {
                    if (isset($table->columns[$name])) {
                        $table->columns[$name]->isPrimaryKey = true;
                    }
                }
            }
        }

        $this->tableSchema = $table;
        $this->columns = $table->columns;

        foreach ($table->columns as $name => $column) {
            if (!$column->isPrimaryKey && null !== $column->defaultValue) {
                $this->attributeDefaults[$name] = $column->defaultValue;
            }
        }

        foreach ($model->relations() as $name => $config) {
            $this->addRelation($name, $config);
        }
    }

    /**
     * 添加关联关系
     * @param string $name
     * @param array $config
     * @throws Exception
     */
    public function addRelation($name, $config)
    {
        if (isset($config[0], $config[1], $config[2])) {
            $this->relations[$name] = new $config[0]($name, $config[1], $config[2], array_slice($config, 3));
        } else {
            throw new Exception(str_cover('"{class}"中存在无效的关联关系配置"{relation}"', [
                '{class}' => $this->_modelClassName,
                '{relation}' => $name,
            ]), 101500202);
        }
    }

    /**
     * 判断是否存在关联关系
     * @param string $name
     * @return bool
     */
    public function hasRelation($name)
    {
        return isset($this->relations[$name]);
    }

    /**
     * 移除某个关联关系
     * @param string $name
     */
    public function removeRelation($name)
    {
        unset($this->relations[$name]);
    }
}