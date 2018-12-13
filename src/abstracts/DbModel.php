<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-13
 * Version      :   1.0
 */

namespace Abstracts;


use Components\Db;
use Components\FileCache;
use DbModelSupports\DbMetaData;
use DbSupports\Builder\Criteria;
use DbSupports\Exception;

abstract class DbModel extends Model
{
    /* @var string belongTo，属于 对应的关系类 */
    const BELONGS_TO = '\DbModelSupports\Relations\BelongsToRelation';
    /* @var string hasOne，拥有（一个） 对应的关系类 */
    const HAS_ONE = '\DbModelSupports\Relations\HasOneRelation';
    /* @var string belongTo，拥有（多个） 对应的关系类 */
    const HAS_MANY = '\DbModelSupports\Relations\HasManyRelation';
    /* @var string belongTo，统计 对应的关系类 */
    const STAT = '\DbModelSupports\Relations\StatRelation';

    /* @var string 唯一验证 */
    const UNIQUE = '\DbModelSupports\Validators\Unique';

    /* @var int 开启缓存时缓存的时间（秒） */
    protected $cachingDuration = 86400;

    /* @var Db 默认的数据库连接 */
    static private $_db;
    /* @var array 元数据store className => DbMetaData */
    static private $_md = [];
    /* @var array db模型实例， className => model */
    static private $_models = [];
    /* @var bool 是否新记录 */
    private $_new = false;
    /* @var array 属性 name => value */
    private $_attributes = [];
    /* @var mixed 模型主键值 */
    private $_pk;
    /* @var array 关联关系模型 relationName => relatedObject */
    private $_related = [];
    /* @var array 程序设置过的字段 */
    private $_updatedAttributes = [];

    /**
     * 获取 db-model 实例
     * @param string $className
     * @return $this
     */
    static public function model($className = null)
    {
        if (null === $className) {
            $className = get_called_class(); // PHP > 5.3.0
        }
        if (!isset(self::$_models[$className])) {
            self::$_models[$className] = new $className(null);
        }
        return self::$_models[$className];
    }

    /**
     * 获取缓存实例
     * @return FileCache|null
     * @throws \Exception
     */
    public function getCache()
    {
        if (class_exists('FileCache')) {
            return FileCache::getInstance([
                'ttl' => '86400',
                'namespace' => 'db-model',
            ]);
        }
        return null;
    }

    /**
     * 构造函数
     * @param string $scenario
     * @throws \Exception
     */
    public function __construct($scenario = 'insert')
    {
        if (null === $scenario) { // internally used by populateRecord() and model()
            return;
        }

        $this->setScenario($scenario);
        $this->setIsNewRecord(true);
        $this->_attributes = $this->getMetaData()->attributeDefaults;
        $this->init();
    }

    /**
     * 获取该模型实例是否为新记录
     * @return bool
     */
    public function getIsNewRecord()
    {
        return $this->_new;
    }

    /**
     * 设置该模型实例是否为新记录
     * @param bool $value
     */
    public function setIsNewRecord($value)
    {
        $this->_new = $value;
    }

    /**
     * 返回当前模型的数据库连接；
     * 如果使用非默认的DB连接，该方法应该被重写
     * @return Db
     * @throws \Exception
     */
    public function getConnection()
    {
        if (null === self::$_db) {
            self::$_db = Db::getInstance([
                'c-file' => 'database',
                'c-group' => 'master',
            ]);
        }
        return self::$_db;
    }

    /**
     * 返回 db-model 的数据表名，该函数可被重载
     * @return string
     */
    public function tableName()
    {
        return '{{' . implode('_', str_explode_by_upper(array_pop(explode('\\', get_class($this))))) . '}}';
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
     * 返回模型的属性名称列表
     * @return array
     * @throws \Exception
     */
    public function attributeNames()
    {
        return array_keys($this->getMetaData()->columns);
    }

    /**
     * 返回 Db 模型的数据表结构
     * @return DbMetaData
     * @throws \Exception
     */
    public function getMetaData()
    {
        $className = get_class($this);
        if (!isset(self::$_md[$className])) {
            if ($this->cachingDuration > 0 && null !== ($cache = $this->getCache())) {
                $_cacheKey = 'db.meta.data.' . get_class($this);
                if (null === ($output = $cache->get($_cacheKey))) {
                    $output = new DbMetaData($this);
                    $cache->set($_cacheKey, $output, $this->cachingDuration);
                }
                self::$_md[$className] = $output;
            } else {
                self::$_md[$className] = new DbMetaData($this);
            }
        }
        return self::$_md[$className];
    }

    /**
     * 获取关联关系的实例
     * @param string $name
     * @param bool|false $refresh
     * @return mixed|null
     * @throws \Exception
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
     * 返回模型的的主键,该方法可以被重写
     * @return array|string
     * @throws \Exception
     */
    public function primaryKey()
    {
        return $this->getMetaData()->tableSchema->primaryKey;
    }

    /**
     * 获取主键的值，若为符合主键，返回 key=>value 数组
     * @return array|mixed|null
     * @throws \Exception
     */
    public function getPrimaryKey()
    {
        $primaryKey = $this->primaryKey();
        if (is_string($primaryKey)) {
            return $this->{$primaryKey};
        } else if (is_array($primaryKey)) {
            $values = [];
            foreach ($primaryKey as $name) {
                $values[$name] = $this->{$name};
            }
            return $values;
        }
        return null;
    }

    /**
     * 设置主键的值
     * @param mixed $value
     * @throws \Exception
     */
    public function setPrimaryKey($value)
    {
        $this->_pk = $this->getPrimaryKey();
        $primaryKey = $this->primaryKey();
        if (is_string($primaryKey)) {
            $this->{$primaryKey} = $value;
        } else if (is_array($primaryKey)) {
            foreach ($primaryKey as $name) {
                $this->{$name} = $value[$name];
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
            return $this->{$name};
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
     * @throws \Exception
     */
    public function getAttributes($names = true)
    {
        $attributes = $this->_attributes;
        foreach ($this->getMetaData()->columns as $name => $column) {
            if (property_exists($this, $name)) {
                $attributes[$name] = $this->{$name};
            } else if (true === $names && !isset($attributes[$name])) {
                $attributes[$name] = null;
            }
        }
        if (is_array($names)) {
            $attrs = [];
            foreach ($names as $name) {
                if (property_exists($this, $name)) {
                    $attrs[$name] = $this->{$name};
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
     * @throws \Exception
     */
    public function setAttribute($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->addUpdateAttributes($name); // 程序更新过的字段
            $this->{$name} = $value;
        } else if (isset($this->getMetaData()->columns[$name])) {
            $this->addUpdateAttributes($name); // 程序更新过的字段
            $this->_attributes[$name] = $value;
        } else {
            return false;
        }
        return true;
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
     * 获取当前空实例
     * @return $this
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
     * @param bool $callAfterFind
     * @return $this|null
     * @throws \Exception
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
                $re->{$name} = $value;
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
     * 创建查询命令
     * @param Criteria $criteria
     * @return \DbSupports\Builder\FindBuilder
     * @throws \Exception
     */
    protected function findBuilder(Criteria $criteria)
    {
        return $this->getConnection()
            ->getFindBuilder()
            ->setTable($this->tableName())
            ->setAlias('t')
            ->addCriteria($criteria);
    }

    /**
     * 根据条件查询模型记录并返回模型实例化
     * @param Criteria $criteria
     * @return $this|null
     * @throws \Exception
     */
    public function find(Criteria $criteria)
    {
        if ($this->beforeFind()) {
            $re = $this->findBuilder($criteria)
                ->setLimit(1)
                ->queryRow();

            return $this->populateRecord($re, true);
        }
        return null;
    }

    /**
     * 根据条件查询模型记录并返回模型实例化
     * @param Criteria $criteria
     * @return $this[]|null
     * @throws \Exception
     */
    public function findAll(Criteria $criteria)
    {
        if ($this->beforeFind()) {
            $res = $this->findBuilder($criteria)
                ->queryAll();
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
     * @return $this|null
     * @throws \Exception
     */
    public function findByAttributes($attributes)
    {
        $criteria = new Criteria();
        $criteria->addWhereByAttributes($attributes);
        return $this->find($criteria);
    }

    /**
     * 根据给定属性查询记录
     * @param array $attributes
     * @return $this[]|null
     * @throws \Exception
     */
    public function findAllByAttributes(array $attributes)
    {
        $criteria = new Criteria();
        $criteria->addWhereByAttributes($attributes);
        return $this->findAll($criteria);
    }

    /**
     * 根据主键查询一条记录
     * @param mixed $pk
     * @return $this|null
     * @throws \Exception
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
     * @return $this[]|null
     * @throws \Exception
     */
    public function findAllByPks(array $pks)
    {
        $criteria = new Criteria();
        $criteria->addWhereIn($this->primaryKey(), $pks);
        return $this->findAll($criteria);
    }

    /**
     * 根据条件查询符合条件的记录数
     * @param Criteria $criteria
     * @return int
     * @throws \Exception
     */
    public function count(Criteria $criteria)
    {
        return $this->findBuilder($criteria)->count();
    }

    /**
     * 查询符合属性的记录数
     * @param array $attributes
     * @return int
     * @throws \Exception
     */
    public function countByAttributes(array $attributes)
    {
        $criteria = new Criteria();
        $criteria->addWhereByAttributes($attributes);
        return $this->count($criteria);
    }

    /**
     * 查询是否有符合条件的记录
     * @param Criteria $criteria
     * @return bool
     * @throws \Exception
     */
    public function exists(Criteria $criteria)
    {
        return $this->count($criteria) > 0;
    }

    /**
     * 查询是否有符合条件的记录
     * @param array $attributes
     * @return bool
     * @throws \Exception
     */
    public function existByAttributes(array $attributes)
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
     * @throws \Exception
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
     * @param array|null $attributes
     * @return bool
     * @throws \Exception
     */
    public function insert($attributes = null)
    {
        if (!$this->getIsNewRecord()) {
            throw new Exception('模型不能重复执行添加操作', 101500101);
        }
        if ($this->beforeInsert()) {
            // 构建插入命令
            $command = $this->getConnection()
                ->getInsertBuilder()
                ->setTable($this->tableName())
                ->setColumns($this->getAttributes($attributes));
            // 插入并获取自增ID
            if ($command->execute()) {
                $table = $this->getMetaData()->tableSchema;
                $primaryKey = $table->primaryKey;
                // 为自增主键添加值
                if (
                    is_string($primaryKey)
                    && null === $this->{$primaryKey}
                    && $table->columns[$primaryKey]->autoIncrement
                ) {
                    $this->{$primaryKey} = $this->getConnection()->getLastInsertId();
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
     */
    protected function afterUpdate()
    {
    }

    /**
     * 更新模型记录
     * @param mixed $attributes
     * @return bool
     * @throws \Exception
     */
    public function update($attributes)
    {
        if ($this->getIsNewRecord()) {
            throw new Exception('新增模型不能使用更新操作', 101500102);
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
     * @param array $columns
     * @param mixed $criteria
     * @return int
     * @throws \Exception
     */
    public function updateByPk($pk, array $columns, $criteria = '')
    {
        return $this->updateAllByAttributes($columns, [
            $this->primaryKey() => $pk,
        ], $criteria);
    }

    /**
     * 根据属性修改数据
     * @param array $columns
     * @param array $attributes
     * @param string $criteria
     * @return int
     * @throws \Exception
     */
    public function updateAllByAttributes($columns, array $attributes, $criteria = '')
    {
        if (!$criteria instanceof Criteria) {
            $criteria = new Criteria([
                'where' => $criteria,
            ]);
        }
        $criteria->addWhereByAttributes($attributes);
        return $this->updateAll($columns, $criteria);
    }

    /**
     * 更新模型中数据记录
     * @param array $columns
     * @param mixed $criteria
     * @return int
     * @throws \Exception
     */
    public function updateAll($columns, $criteria)
    {
        return $this->getConnection()
            ->getUpdateBuilder()
            ->setTable($this->tableName())
            ->setWhere($criteria)
            ->setColumns($columns)
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
     * @throws \Exception
     */
    public function delete()
    {
        if ($this->getIsNewRecord()) {
            throw new Exception('新增模型不能使用删除操作', 101500103);
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
     * @return int
     * @throws \Exception
     */
    public function deleteByPk($pk, $criteria = '')
    {
        return $this->deleteAllByAttributes([
            $this->primaryKey() => $pk,
        ], $criteria);
    }

    /**
     * 删除对应属性的记录
     * @param array $attributes
     * @param string $criteria
     * @return int
     * @throws \Exception
     */
    public function deleteAllByAttributes(array $attributes, $criteria = '')
    {
        if (!$criteria instanceof Criteria) {
            $criteria = new Criteria([
                'where' => $criteria,
            ]);
        }
        $criteria->addWhereByAttributes($attributes);
        return $this->deleteAll($criteria);
    }

    /**
     * 删除符合条件的记录
     * @param string|Criteria $criteria
     * @return int
     * @throws \Exception
     */
    public function deleteAll($criteria)
    {
        return $this->getConnection()
            ->getDeleteBuilder()
            ->setTable($this->tableName())
            ->setWhere($criteria)
            ->execute();
    }

    /**
     * __get：魔术方法，当直接访问属性不存在时被唤醒
     * @param string $property
     * @return mixed
     * @throws \Exception
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
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        if (false === $this->setAttribute($name, $value)) {
            if (isset($this->getMetaData()->relations[$name])) {
                $this->_related[$name] = $value;
            } else {
                parent::__set($name, $value);
            }
        }
    }
}