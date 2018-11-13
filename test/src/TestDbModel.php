<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-11
 * Version      :   1.0
 */

namespace Test;

use Db\Builder\Criteria;
use TestClass\Stu;
use TestClass\StuCourse;
use TestCore\Tester;

class TestDbModel extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run()
    {
        // 获取数据库模型
//        $model = new StuCourse();
        $model = new Stu();

        // 返回 Db 模型的数据表结构
//        $metaData = $model->getMetaData();
//        var_dump($metaData);

        // 获取数据表模型属性（字段）
//        $attributeNames = $model->attributeNames();
//        var_dump($attributeNames);

        // 获取模型主键
//        $primaryKey = $model->primaryKey();
//        var_dump($primaryKey);

        // 设置模型主键的值
//        $model->setPrimaryKey(11);

        // 获取模型主键值
//        $primaryValue = $model->getPrimaryKey();
//        var_dump($primaryValue);

        // 通过 Criteria 查询
        $criteria = new Criteria();
        $stu = $model->find($criteria);

        $relate = $stu->getRelated('course');

        var_dump($relate);

        exit;

        // 设置属性值
        $model->setAttributes([
            'name' => 'xx',
            'sex' => 'xe',
        ]);

        // 获取属性值
        $attributes = $model->getAttributes();
        var_dump($attributes);

        var_dump(1111111111);

    }
}