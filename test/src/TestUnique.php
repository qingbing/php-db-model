<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-14
 * Version      :   1.0
 */

namespace Test;

use TestClass\StuClass;
use TestCore\Tester;

class TestUnique extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \Exception
     */
    public function run()
    {
        $model = new StuClass();

        $model->setAttributes([
            'name' => '二年级',
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