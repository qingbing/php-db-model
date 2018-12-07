<?php

/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-12
 * Version      :   1.0
 */

namespace DbModel\Relation;

use DbModel\Abstracts\DbRelation;

class HasManyRelation extends DbRelation
{
    /**
     * 获取关联模型或结果
     * @param \DbModel $model
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function getResult($model, $params = [])
    {
        return $this->findByParams($model, $params, 'findAll');
    }
}