<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-07
 * Version      :   1.0
 */

namespace DbModel\Abstracts;

use Abstracts\Base;
use Db\Builder\Criteria;

abstract class DbRelation extends Base
{
    /* @var string 关联名称 */
    public $name;
    /* @var string 关联的模型类 */
    public $className;
    /* @var string 关联模型的关键字段（目前只支持单字段关联） */
    public $foreignKey;
    /* @var string 与关联模型关联的字段 */
    public $primaryKey;

    /* @var bool 是否去重 */
    public $distinct = false;
    /* @var mixed 需要删选关联模型的字段 */
    public $select = '*';
    /* @var string 关联的连接方式 */
    public $join = '';
    /* @var string 关联的 group */
    public $group = '';
    /* @var string 关联的 having */
    public $having = '';
    /* @var string 关联的 order */
    public $order = '';
    /* @var int 关联的 order */
    public $limit = -1;
    /* @var mixed 其他关联的条件 */
    public $condition = '';
    /* @var array 关联查询的 params */
    public $params = [];

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
            $this->{$name} = $value;
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
     * @param \DbModel $model
     * @param array $params
     * @param string $method
     * @return mixed
     * @throws \Exception
     */
    protected function findByParams($model, $params = [], $method)
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
     * @param \DbModel $model
     * @param array $params
     * @return mixed
     */
    abstract public function getResult($model, $params = []);
}