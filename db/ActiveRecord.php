<?php
/**
 * @date        2017-12-15
 * @author      qingbing<780042175@qq.com>
 * @version     1.0
 */

namespace pf\db;

use pf\core\Core;
use pf\core\Model;
use pf\helper\Unit;
use pf\PFBase;

class ActiveRecord extends Model
{


    private $_updatedAttributes = []; // 程序设置过的字段



    /**
     * 获取设置过更新的 db-record-attribute
     * @return array
     */
    protected function getUpdatedAttributes()
    {
        return array_keys($this->_updatedAttributes);
    }

    /**
     * 添加设置过更新的 db-record-attribute
     * @param string $attributeName
     */
    protected function addUpdateAttributes($attributeName)
    {
        $this->_updatedAttributes[$attributeName] = 1;
    }

    /**
     * 在数据保存之前执行
     * @return bool
     */
    protected function beforeSave()
    {
        return true;
    }

    /**
     * 在数据保存之后执行
     */
    protected function afterSave()
    {
    }

    /**
     * 保存模型数据记录
     * @param bool|true $runValidation
     * @param mixed $attributes
     * @return bool
     * @throws DbException
     */
    public function save($runValidation = true, $attributes = null)
    {
        if (!$runValidation || $this->validate($attributes)) {
            if ($this->beforeSave()) {
                $r = $this->getIsNewRecord() ? $this->insert($attributes) : $this->update($attributes);
                if ($r) {
                    $this->afterSave();
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 在数据插入之前执行
     * @return bool
     */
    protected function beforeInsert()
    {
        return true;
    }

    /**
     * 在数据插入之后执行
     */
    protected function afterInsert()
    {
    }

    /**
     * 插入新数据
     * @param null|array $attributes
     * @return bool
     * @throws DbException
     */
    public function insert($attributes = null)
    {
        if (!$this->getIsNewRecord()) {
            throw new DbException(Unit::replace('The active record cannot be inserted to database because it is not new.'));
        }
        if ($this->beforeInsert()) {
            // 构建插入命令
            $command = $this->getConnection()
                ->createInsertCommand()
                ->setTable($this->tableName())
                ->setData($this->getAttributes($attributes));
            // 插入并获取自增ID
            if (false !== $command->execute()) {
                $table = $this->getMetaData()->tableSchema;
                $primaryKey = $table->primaryKey;
                // 为自增主键添加值
                if (
                    is_string($primaryKey)
                    && null === $this->{$primaryKey}
                    && $table->columns[$primaryKey]->autoIncrement
                ) {
                    $this->{$primaryKey} = $command->getLastInsertId();
                }
                $this->_pk = $this->getPrimaryKey();
                $this->setIsNewRecord(false);
                $this->setScenario('update');
                $this->afterInsert();
                return true;
            }
        }
        return false;
    }

    /**
     * 在数据更新之前执行
     * @return bool
     */
    protected function beforeUpdate()
    {
        return true;
    }

    /**
     * 在数据更新之后执行
     * @return bool
     */
    protected function afterUpdate()
    {
    }

    /**
     * 更新模型记录
     * @param mixed $attributes
     * @return bool
     * @throws DbException
     */
    public function update($attributes)
    {
        if ($this->getIsNewRecord()) {
            throw new DbException(Unit::replace('The active record cannot be updated to database because it is new.'));
        }
        if ($this->beforeUpdate()) {
            if (null === $this->_pk) {
                $this->_pk = $this->getPrimaryKey();
            }
            if (null === $attributes) {
                $attributes = $this->getUpdatedAttributes();
            }
            if (false !== $this->updateByPk($this->getOldPrimaryKey(), $this->getAttributes($attributes))) {
                $this->_pk = $this->getPrimaryKey();
                $this->afterUpdate();
                return true;
            }
        }
        return false;
    }

    /**
     * 根据主键更新记录
     * @param mixed $pk
     * @param array $data
     * @param mixed $criteria
     * @param array $params
     * @return int
     * @throws DbException
     */
    public function updateByPk($pk, array $data, $criteria = '', $params = [])
    {
        return $this->updateAllByAttributes($data, [
            $this->quoteColumnName($this->primaryKey()) => $pk,
        ], $criteria, $params);
    }

    /**
     * 根据属性修改对应的值
     * @param array $data
     * @param array $attributes
     * @param string $criteria
     * @param array $params
     * @return int
     * @throws DbException
     */
    public function updateAllByAttributes($data, array $attributes, $criteria = '', $params = [])
    {
        if (!$criteria instanceof Criteria) {
            $criteria = new Criteria([
                'where' => $criteria,
            ]);
        }
        $criteria->addWhereByAttributes($attributes);
        return $this->updateAll($data, $criteria, $params);
    }

    /**
     * 更新模型中数据记录
     * @param array $data
     * @param mixed $criteria
     * @param array $params
     * @return int
     * @throws DbException
     */
    public function updateAll($data, $criteria, $params = [])
    {
        return $this->getConnection()
            ->createUpdateCommand()
            ->setTable($this->tableName())
            ->setWhere($criteria)
            ->setData($data)
            ->addParams($params)
            ->execute();
    }

    /**
     * 在数据删除之前执行
     * @return bool
     */
    protected function beforeDelete()
    {
        return true;
    }

    /**
     * 在数据删除之后执行
     */
    protected function afterDelete()
    {
    }

    /**
     * 删除当前模型对应记录
     * @return bool
     * @throws DbException
     */
    public function delete()
    {
        if ($this->getIsNewRecord()) {
            throw new DbException(Unit::replace('The active record cannot be deleted because it is new.'));
        }
        if ($this->beforeDelete() && false !== $this->deleteByPk($this->getPrimaryKey())) {
            $this->afterDelete();
            return true;
        }
        return false;
    }

    /**
     * 删除对应主键的记录
     * @param mixed $pk
     * @param string|Criteria $criteria
     * @param array $params
     * @return int
     */
    public function deleteByPk($pk, $criteria = '', $params = [])
    {
        return $this->deleteAllByAttributes([
            $this->quoteColumnName($this->primaryKey()) => $pk,
        ], $criteria, $params);
    }

    /**
     * 删除对应属性的记录
     * @param array $attributes
     * @param string|Criteria $criteria
     * @param array $params
     * @return int
     */
    public function deleteAllByAttributes(array $attributes, $criteria = '', $params = [])
    {
        if (!$criteria instanceof Criteria) {
            $criteria = new Criteria([
                'where' => $criteria,
            ]);
        }
        $criteria->addWhereByAttributes($attributes);
        return $this->deleteAll($criteria, $params);
    }

    /**
     * 删除符合条件的记录
     * @param string|Criteria $criteria
     * @param array $params
     * @return int
     * @throws DbException
     */
    public function deleteAll($criteria, $params = [])
    {
        return $this->getConnection()
            ->createDeleteCommand()
            ->setTable($this->tableName())
            ->setWhere($criteria)
            ->addParams($params)
            ->execute();
    }
}