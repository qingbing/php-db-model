<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-11
 * Version      :   1.0
 */

namespace Test;

use DBootstrap\Abstracts\Tester;
use TestClass\Stu;

class TestAddModel extends Tester
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

        // 设置模型数据
        $model->setAttributes([
            'name' => 'insertName',
            'class_id' => '4',
            'sex' => '1',
        ]);

        // 保存判断
        if ($model->save()) {
            var_dump('save success');
            var_dump($model);
        } else {
            var_dump($model->getErrors());
        }
    }
}