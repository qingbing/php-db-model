<?php
/**
 * @date        2017-12-14
 * @author      qingbing<780042175@qq.com>
 * @version     1.0
 */

namespace pf\db\mysql;

class Driver extends \pf\db\Driver
{
    /**
     * 获取指定表的信息
     * @param string $name
     * @return TableSchema driver dependent table metadata. Null if the table does not exist.
     */
    protected function loadTable($name)
    {
        $tableSchema = new TableSchema();
        $this->resolveTableNames($tableSchema, $name);
        if ($this->findColumns($tableSchema)) {
            return $tableSchema;
        }
        return null;
    }

    /**
     * 构建表名
     * @param TableSchema $tableSchema
     * @param string $name
     */
    protected function resolveTableNames($tableSchema, $name)
    {
        $parts = explode('.', str_replace(['`', '"'], '', $name));
        if (isset($parts[1])) {
            $tableSchema->schemaName = $parts[0];
            $tableSchema->name = $parts[1];
            $tableSchema->rawName = $this->quoteTableName($tableSchema->schemaName) . '.' . $this->quoteTableName($tableSchema->name);
        } else {
            $tableSchema->name = $parts[0];
            $tableSchema->rawName = $this->quoteTableName($tableSchema->name);
        }
    }

    /**
     * 收集表信息
     * @param TableSchema $tableSchema
     * @return bool
     */
    protected function findColumns($tableSchema)
    {
        $sql = 'SHOW FULL COLUMNS FROM ' . $tableSchema->rawName;
        try {
            $columns = $this->createCommand()
                ->setText($sql)
                ->queryAll();
        } catch (\Exception $e) {
            return false;
        }
        foreach ($columns as $column) {
            $c = $this->createColumn($column);
            $tableSchema->columns[$c->name] = $c;
            if ($c->isPrimaryKey) {
                if (null === $tableSchema->primaryKey) {
                    $tableSchema->primaryKey = $c->name;
                } else if (is_string($tableSchema->primaryKey)) {
                    $tableSchema->primaryKey = [$tableSchema->primaryKey, $c->name];
                } else {
                    $tableSchema->primaryKey[] = $c->name;
                }
                if ($c->autoIncrement) {
                    $tableSchema->sequenceName = '';
                }
            }
        }
        return true;
    }

    /**
     * 创建 table-column
     * @param array $column
     * @return ColumnSchema
     */
    protected function createColumn($column)
    {
        $c = new ColumnSchema();
        $c->name = $column['Field'];
        $c->rawName = $this->quoteColumnName($c->name);
        $c->allowNull = $column['Null'] === 'YES';
        $c->isPrimaryKey = false !== strpos($column['Key'], 'PRI');
        $c->isForeignKey = false;
        $c->init($column['Type'], $column['Default']);
        $c->autoIncrement = false !== strpos(strtolower($column['Extra']), 'auto_increment');
        if (isset($column['Comment'])) {
            $c->comment = $column['Comment'];
        }
        return $c;
    }
}