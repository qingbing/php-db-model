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


    private $_pk; // 模型主键值
    private $_related = []; // 属性 relationName => relatedObject
    private $_updatedAttributes = []; // 程序设置过的字段

    /**
     * 引号包裹字段名称，带表名
     * @param string $name
     * @return string
     */
    public function quoteColumnName($name)
    {
        return $this->getConnection()->getDriver()->quoteColumnName($name);
    }

    /**
     * 引号包裹表名称，带库名
     * @param string $name
     * @return string
     */
    public function quoteTableName($name)
    {
        return $this->getConnection()->getDriver()->quoteTableName($name);
    }

    /**
     * 返回模型的的主键,该方法可以被重写
     * @return mixed
     */
    public function primaryKey()
    {
        return $this->getMetaData()->tableSchema->primaryKey;
    }

    /**
     * 返回模型的属性名称列表
     * @return array
     */
    public function attributeNames()
    {
        return array_keys($this->getMetaData()->columns);
    }

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
     * 返回指定属性的值
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        } else if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        } else {
            return null;
        }
    }

    /**
     * 返回属性值
     * @param array|bool $names
     * @return array attribute values (name=>value).
     */
    public function getAttributes($names = true)
    {
        $attributes = $this->_attributes;
        foreach ($this->getMetaData()->columns as $name => $column) {
            if (property_exists($this, $name)) {
                $attributes[$name] = $this->$name;
            } else if (true === $names && !isset($attributes[$name])) {
                $attributes[$name] = null;
            }
        }
        if (is_array($names)) {
            $attrs = [];
            foreach ($names as $name) {
                if (property_exists($this, $name)) {
                    $attrs[$name] = $this->$name;
                } else {
                    $attrs[$name] = isset($attributes[$name]) ? $attributes[$name] : null;
                }
            }
            return $attrs;
        } else {
            return $attributes;
        }
    }

    /**
     * 设置指定属性的值
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function setAttribute($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->addUpdateAttributes($name); // 程序更新过的字段
            $this->$name = $value;
        } else if (isset($this->getMetaData()->columns[$name])) {
            $this->addUpdateAttributes($name); // 程序更新过的字段
            $this->_attributes[$name] = $value;
        } else {
            return false;
        }
        return true;
    }

    /**
     * 获取主键的值，若为符合主键，返回 key=>value 数组
     * @return array|mixed|null
     */
    public function getPrimaryKey()
    {
        $primaryKey = $this->primaryKey();
        if (is_string($primaryKey)) {
            return $this->{$primaryKey};
        } else if (is_array($primaryKey)) {
            $values = [];
            foreach ($primaryKey as $name) {
                $values[$name] = $this->$name;
            }
            return $values;
        }
        return null;
    }

    /**
     * 设置主键的值
     * @param mixed $value
     */
    public function setPrimaryKey($value)
    {
        $this->_pk = $this->getPrimaryKey();
        $primaryKey = $this->primaryKey();
        if (is_string($primaryKey)) {
            $this->{$primaryKey} = $value;
        } else if (is_array($primaryKey)) {
            foreach ($primaryKey as $name) {
                $this->$name = $value[$name];
            }
        }
    }

    /**
     * 返回当前模型的主键值
     * @return mixed
     */
    public function getOldPrimaryKey()
    {
        return $this->_pk;
    }

    /**
     * 设置当前模型的主键值
     * @param mixed $value
     */
    public function setOldPrimaryKey($value)
    {
        $this->_pk = $value;
    }

    /**
     * 定义模型的关联关系
     * 'relationName' => [self::HAS_MANY, className, 'foreignKey', optionKey => value, ...]
     * 'relationName' => [self::HAS_ONE, className, 'foreignKey', optionKey => value, ...]
     * 'relationName' => [self::BELONGS_TO, className, 'primaryKey', optionKey => value, ...]
     * 'relationName' => [self::STAT, className, 'foreignKey', optionKey => value, ...]
     * @return array
     */
    public function relations()
    {
        return [];
    }

    /**
     * 获取关联关系的实例
     * @param string $name
     * @param bool|false $refresh
     * @return mixed|null
     */
    public function getRelated($name, $refresh = false)
    {
        if (!$refresh && isset($this->_related[$name])) {
            return $this->_related[$name];
        }
        $md = $this->getMetaData();
        if (!isset($md->relations[$name]) || $this->getIsNewRecord()) {
            return null;
        }
        return $this->_related[$name] = $md->relations[$name]->getResult($this);
    }

    /**
     * 创建查询命令
     * @param Criteria $criteria
     * @return FindCommand
     * @throws DbException
     */
    protected function createFindCommand(Criteria $criteria)
    {
        $command = $this->getConnection()
            ->createFindCommand()
            ->setAlias('t')
            ->setTable($this->tableName())
            ->addCriteria($criteria);
        return $command;
    }

    /**
     * 获取当前空实例
     * @return ActiveRecord
     */
    protected function instantiate()
    {
        $class = get_class($this);
        $model = new $class(null);
        return $model;
    }

    /**
     * 通过属性值实例化模型
     * @param array $attributes
     * @param bool|true $callAfterFind
     * @return ActiveRecord|null
     */
    protected function populateRecord($attributes, $callAfterFind = true)
    {
        if (false === $attributes) {
            return null;
        }
        $re = $this->instantiate();
        $re->setScenario('update');
        $md = $re->getMetaData();
        foreach ($attributes as $name => $value) {
            if (property_exists($re, $name)) {
                $re->$name = $value;
            } else if (isset($md->columns[$name])) {
                $re->_attributes[$name] = $value;
            }
        }
        $re->_pk = $re->getPrimaryKey();
        if ($callAfterFind) {
            $re->afterFind();
        }
        return $re;
    }

    /**
     * 在查询数据之前执行
     * @return bool
     */
    protected function beforeFind()
    {
        return true;
    }

    /**
     * 在查询数据之后执行
     */
    protected function afterFind()
    {
    }

    /**
     * 根据条件查询模型记录并返回模型实例化
     * @param Criteria $criteria
     * @param array $params
     * @return ActiveRecord|null
     */
    public function find(Criteria $criteria, $params = [])
    {
        if ($this->beforeFind()) {
            $command = $this->createFindCommand($criteria);
            $command->setLimit(1);
            $re = $command->queryRow($params);
            return $this->populateRecord($re, true);
        }
        return null;
    }

    /**
     * 根据条件查询模型记录并返回模型实例化
     * @param Criteria $criteria
     * @param array $params
     * @return array|null
     */
    public function findAll(Criteria $criteria, $params = [])
    {
        if ($this->beforeFind()) {
            $command = $this->createFindCommand($criteria);
            $res = $command->queryAll($params);
            foreach ($res as &$re) {
                $re = $this->populateRecord($re, true);
            }
            unset($re);
            return $res;
        }
        return null;
    }

    /**
     * 根据给定属性查询记录
     * @param array $attributes
     * @return ActiveRecord|null
     */
    public function findByAttributes($attributes)
    {
        $criteria = new Criteria();
        $attrs = [];
        foreach ($attributes as $f => $v) {
            $attrs[$this->quoteColumnName($f)] = $v;
        }
        $criteria->addWhereByAttributes($attrs);
        return $this->find($criteria);
    }

    /**
     * 根据给定属性查询记录
     * @param array $attributes
     * @return ActiveRecord[]|null
     */
    public function findAllByAttributes($attributes)
    {
        $criteria = new Criteria();
        $attrs = [];
        foreach ($attributes as $f => $v) {
            $attrs[$this->quoteColumnName($f)] = $v;
        }
        $criteria->addWhereByAttributes($attrs);
        return $this->findAll($criteria);
    }

    /**
     * 根据主键查询一条记录
     * @param mixed $pk
     * @return ActiveRecord|null
     */
    public function findByPk($pk)
    {
        return $this->findByAttributes([
            $this->primaryKey() => $pk,
        ]);
    }

    /**
     * 根据主键查询记录
     * @param array $pks
     * @return ActiveRecord[]|null
     */
    public function findAllByPks($pks)
    {
        return $this->findAllByAttributes([
            $this->primaryKey() => $pks,
        ]);
    }

    /**
     * 根据条件查询符合条件的记录数
     * @param Criteria $criteria
     * @param array $params
     * @return int
     */
    public function count(Criteria $criteria, $params = [])
    {
        $command = $this->createFindCommand($criteria);
        return $command->queryCount($params);
    }

    /**
     * 查询符合属性的记录数
     * @param $attributes
     * @return int
     */
    public function countByAttributes($attributes)
    {
        $criteria = new Criteria();
        $attrs = [];
        foreach ($attributes as $f => $v) {
            $attrs[$this->quoteColumnName($f)] = $v;
        }
        $criteria->addWhereByAttributes($attrs);
        return $this->count($criteria);
    }

    /**
     * 查询是否有符合条件的记录
     * @param Criteria $criteria
     * @param array $params
     * @return bool
     */
    public function exists(Criteria $criteria, $params = [])
    {
        return $this->count($criteria, $params) > 0;
    }

    /**
     * 查询是否有符合条件的记录
     * @param array $attributes
     * @return bool
     */
    public function existByAttributes($attributes)
    {
        return $this->countByAttributes($attributes) > 0;
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

    /**
     * __get：魔术方法，当直接访问属性不存在时被唤醒
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        if (isset($this->_attributes[$property])) {
            return $this->_attributes[$property];
        } else if (isset($this->getMetaData()->columns[$property])) {
            return null;
        } else if (isset($this->_related[$property])) {
            return $this->_related[$property];
        } else if (isset($this->getMetaData()->relations[$property])) {
            return $this->getRelated($property);
        } else {
            return parent::__get($property);
        }
    }

    /**
     * __set：魔术方法，当直接设置不存在属性时被唤醒
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (false === $this->setAttribute($name, $value)) {
            if (isset($this->getMetaData()->relations[$name]))
                $this->_related[$name] = $value;
            else
                parent::__set($name, $value);
        }
    }
}

/**
 * Class ActiveRecordMetaData
 * @package pf\db
 */
class ActiveRecordMetaData extends Core
{
    /**
     * @var BaseActiveRelation[]
     */
    public $relations = []; // 外部模型关联关系
    public $attributeDefaults = []; // 属性默认值
    /**
     * @var TableSchema
     */
    public $tableSchema;
    /**
     * @var []ColumnSchema
     */
    public $columns;

    /**
     * 添加关联关系
     * @param string $name
     * @param array $config
     * @throws DbException
     */
    public function addRelation($name, $config)
    {
        if (isset($config[0], $config[1], $config[2])) {
            $this->relations[$name] = new $config[0]($name, $config[1], $config[2], array_slice($config, 3));
        } else {
            throw new DbException(Unit::replace('Active record "{class}" has an invalid configuration for relation "{relation}".', [
                '{class}' => $this->_modelClassName,
                '{relation}' => $name,
            ]));
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

/**
 * Class BaseActiveRelation
 * @package pf\db
 */
abstract class BaseActiveRelation extends Core
{
    public $name; // 关联名称
    public $className; // 关联的模型类
    public $foreignKey; // 关联模型的关键字段（目前只支持单字段关联）
    public $primaryKey; // 与关联模型关联的字段

    public $distinct = false; // 是否去重
    public $select = '*'; // 需要删选关联模型的字段
    public $join = ''; // 关联的连接方式
    public $group = ''; // 关联的 group
    public $having = ''; // 关联的 having
    public $order = ''; // 关联的 order
    public $limit = -1; // 关联的 order
    public $condition = ''; // 其他关联的条件
    public $params = []; // 关联查询的 params

    /**
     * 构造函数
     * @param string $name 关联名称
     * @param string $className 关联模型类名
     * @param string $foreignKey 关联外键
     * @param array $options 其他关联属性
     */
    public function __construct($name, $className, $foreignKey, $options = [])
    {
        $this->name = $name;
        $this->className = $className;
        $this->foreignKey = $foreignKey;
        foreach ($options as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * 创建查询的标准条件
     * @param array $params
     * @return Criteria
     */
    protected function createCriteria($params = [])
    {
        $criteria = new Criteria();
        $criteria->setDistinct(isset($params['distinct']) ? $params['distinct'] : $this->distinct);
        $criteria->setSelect(isset($params['select']) ? $params['select'] : $this->select);
        $criteria->setJoin(isset($params['join']) ? $params['join'] : $this->join);
        $criteria->setGroup(isset($params['group']) ? $params['group'] : $this->group);
        $criteria->setHaving(isset($params['having']) ? $params['having'] : $this->having);
        $criteria->setOrder(isset($params['order']) ? $params['order'] : $this->order);
        $criteria->setLimit(isset($params['limit']) ? $params['limit'] : $this->limit);
        $criteria->setWhere(isset($params['where']) ? $params['where'] : $this->condition);
        $criteria->setParams(isset($params['params']) ? $params['params'] : $this->params);
        return $criteria;
    }

    /**
     * 查找关系结果
     * @param ActiveRecord $model
     * @param string $params
     * @param string $method
     * @return mixed
     */
    protected function findByParams($model, $params, $method)
    {
        $ar = new $this->className();
        $criteria = $this->createCriteria($params);
        if (null === $this->primaryKey) {
            $criteria->addWhereByAttributes([
                $this->foreignKey => $model->{$model->primaryKey()},
            ]);
        } else {
            $criteria->addWhereByAttributes([
                $this->foreignKey => $model->{$this->primaryKey},
            ]);
        }
        return $ar->{$method}($criteria);
    }

    /**
     * 获取关联模型或结果
     * @param ActiveRecord $model
     * @param array $params
     * @return mixed
     */
    abstract public function getResult($model, $params = []);
}

/**
 * Class BelongsToRelation
 * @package pf\db
 */
class BelongsToRelation extends BaseActiveRelation
{
    /**
     * 获取关联模型或结果
     * @param ActiveRecord $model
     * @param array $params
     * @return mixed
     */
    public function getResult($model, $params = [])
    {
        $ar = new $this->className();
        /* @var ActiveRecord $ar */
        $criteria = $this->createCriteria($params);
        if (null === $this->primaryKey) {
            $criteria->addWhereByAttributes([
                $ar->primaryKey() => $model->{$this->foreignKey},
            ]);
        } else {
            $criteria->addWhereByAttributes([
                $this->primaryKey => $model->{$this->foreignKey},
            ]);
        }
        return $ar->find($criteria);
    }
}

/**
 * Class HasOneRelation
 * @package pf\db
 */
class HasOneRelation extends BaseActiveRelation
{
    /**
     * 获取关联模型或结果
     * @param ActiveRecord $model
     * @param array $params
     * @return mixed
     */
    public function getResult($model, $params = [])
    {
        return $this->findByParams($model, $params, 'find');
    }
}

/**
 * Class HasManyRelation
 * @package pf\db
 */
class HasManyRelation extends BaseActiveRelation
{
    /**
     * 获取关联模型或结果
     * @param ActiveRecord $model
     * @param array $params
     * @return mixed
     */
    public function getResult($model, $params = [])
    {
        return $this->findByParams($model, $params, 'findAll');
    }
}

/**
 * Class StatRelation
 * @package pf\db
 */
class StatRelation extends BaseActiveRelation
{
    /**
     * 获取关联模型或结果
     * @param ActiveRecord $model
     * @param array $params
     * @return mixed
     */
    public function getResult($model, $params = [])
    {
        return $this->findByParams($model, $params, 'count');
    }
}