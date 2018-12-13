<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-13
 * Version      :   1.0
 */

namespace DbModelSupports\Relations;


use DbModelSupports\Abstracts\Relation;

class BelongsToRelation extends Relation
{
    /**
     * 获取关联模型或结果
     * @param \Abstracts\DbModel $model
     * @param array $params
     * @return \Abstracts\DbModel|mixed|null
     * @throws \Exception
     */
    public function getResult($model, $params = [])
    {
        $ar = new $this->className();
        /* @var \Abstracts\DbModel $ar */
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