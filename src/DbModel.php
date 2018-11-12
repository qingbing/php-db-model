<?php

use DbModel\DbMetaData;
use Model\Model;

/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-12
 * Version      :   1.0
 */
class DbModel extends Model
{
    /* @var string belongTo，属于 对应的关系类 */
    const BELONGS_TO = '\DbModel\BelongsToRelation';
    /* @var string hasOne，拥有（一个） 对应的关系类 */
    const HAS_ONE = '\DbModel\HasOneRelation';
    /* @var string belongTo，拥有（多个） 对应的关系类 */
    const HAS_MANY = '\DbModel\HasManyRelation';
    /* @var string belongTo，统计 对应的关系类 */
    const STAT = '\DbModel\StatRelation';

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

    /* @var int 开启缓存时缓存的时间（秒） */
    protected $cachingDuration = 86400;

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
     * @return CacheFile|null
     * @throws Exception
     */
    public function getCache()
    {
        if (class_exists('CacheFile')) {
            return CacheFile::getInstance('db-model');
        }
        return null;
    }

    /**
     * 构造函数
     * @param string $scenario
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
     * @throws Exception
     */
    public function getConnection()
    {
        if (null === self::$_db) {
            self::$_db = \Db::getInstance();
        }
        return self::$_db;
    }

    /**
     * 返回 AR 模型的元数据
     * @return DbMetaData
     * @throws Exception
     */
    public function getMetaData()
    {
        $className = get_class($this);
        if (!isset(self::$_md[$className])) {
            if ($this->cachingDuration > 0 && null !== ($cache = $this->getCache())) {
                $_cacheKey = 'db.meta.data.' . get_class($this);
                if (false === ($output = $cache->get($_cacheKey))) {
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
     * 返回模型的属性名称列表
     * @return array
     */
    public function attributeNames()
    {
        // TODO: Implement attributeNames() method.
    }
}