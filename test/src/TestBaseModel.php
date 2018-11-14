<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-11
 * Version      :   1.0
 */

namespace Test;

use TestClass\Stu;
use TestCore\Tester;

class TestBaseModel extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \Exception
     */
    public function run()
    {
        // 新增模型数据
        $model = new Stu();
        // 设置字段属性
        $model->setAttributes([
            'name' => 'insertName',
            'class_id' => '4',
            'sex' => '1',
        ]);
        // 获取字段数据
        var_dump($model->name);
        var_dump($model->getAttribute('name'));
        // 获取所有字段数据
        var_dump($model->getAttributes());
    }
}