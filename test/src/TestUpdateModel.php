<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-11
 * Version      :   1.0
 */

namespace Test;

use DBootstrap\Abstracts\Tester;
use DbSupports\Builder\Criteria;
use TestClass\Stu;

class TestUpdateModel extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \Exception
     */
    public function run()
    {
        // 通过 Criteria 更新数据
        $criteria = new Criteria();
        $criteria->addWhere('`id`>=:minId', [
            ':minId' => 7
        ]);
        $num = Stu::model()->updateAll([
            'name' => 'updateAll',
        ], $criteria);
        var_dump($num);

        // 根据属性修改数据
        $num = Stu::model()->updateAllByAttributes([
            'name' => 'updateAllByAttributes',
        ], [
            'id' => 7
        ]);
        var_dump($num);

        // 根据主键更新记录
        $num = Stu::model()->updateByPk(8, [
            'name' => 'updateByPk',
        ]);
        var_dump($num);

        // 模型查询
        $model = Stu::model()->findByPk('6');
        $model->setAttributes([
            'name' => 'updateSave',
        ]);
        if ($model->save()) {
            var_dump('save success');
        } else {
            var_dump($model->getErrors());
        }
    }
}