<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-13
 * Version      :   1.0
 */

namespace DbModelSupports\Relations;


use DbModelSupports\Abstracts\Relation;

class HasManyRelation extends Relation
{
    /**
     * 获取关联模型或结果
     * @param \Abstracts\DbModel $model
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function getResult($model, $params = [])
    {
        return $this->findByParams($model, $params, 'findAll');
    }
}