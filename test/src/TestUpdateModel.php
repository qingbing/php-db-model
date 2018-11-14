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

class TestUpdateModel extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \Exception
     */
    public function run()
    {
        $model = Stu::model()->findByPk('7');
        $model->setAttributes([
            'name' => 'update',
        ]);
        if ($model->validate()) {
            var_dump($model->save());
        } else {
            var_dump($model->getErrors());
        }
    }
}