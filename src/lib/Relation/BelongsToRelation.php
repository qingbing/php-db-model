<?php

/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-12
 * Version      :   1.0
 */

namespace DbModel\Relation;


use DbModel\DbRelation;

class BelongsToRelation extends DbRelation
{
    /**
     * 获取关联模型或结果
     * @param \DbModel $model
     * @param array $params
     * @return \DbModel|mixed|null
     * @throws \Exception
     */
    public function getResult($model, $params = [])
    {
        $ar = new $this->className();
        /* @var \DbModel $ar */
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