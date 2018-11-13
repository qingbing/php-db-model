<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 2018/11/12
 * Time: 上午11:09
 */

namespace DbModel;


use DbModel;
use Helper\Base;

class DbMetaData extends Base
{
    private $_modelClassName;


    /**
     * 构造函数
     * @param DbModel $model
     * @throws DbException
     */
    public function __construct(DbModel $model)
    {
        $this->_modelClassName = get_class($model);
        $tableName = $model->tableName();

        var_dump($tableName);
        exit;
        if (null === ($table = $model->getConnection()->getDriver()->getTable($tableName))) {
            throw new DbException(Unit::replace('The table "{table}" for active record class "{class}" cannot be found in the database.', [
                '{class}' => $this->_modelClassName,
                '{table}' => $tableName,
            ]));
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
}