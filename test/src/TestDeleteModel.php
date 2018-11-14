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
use TestCore\Tester;

class TestDeleteModel extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \Exception
     */
    public function run()
    {
        // 通过 Criteria 删除数据
        $criteria = new Criteria();
        $criteria->addWhere('`id`>=:minId', [
            ':minId' => 13
        ]);
        $num = Stu::model()->deleteAll($criteria);
        var_dump($num);

        // 删除对应属性的记录
        $num = Stu::model()->deleteAllByAttributes([
            'id' => 12
        ]);
        var_dump($num);

        // 删除对应主键的记录
        $num = Stu::model()->deleteByPk(11);
        var_dump($num);

        // 通过模型删除记录
        $stu = Stu::model()->findByPk(10);
        if ($stu) {
            if ($stu->delete()) {
                var_dump('delete success');
            } else {
                var_dump('delete failure');
            }
        } else {
            var_dump('record has been deleted');
        }
    }
}