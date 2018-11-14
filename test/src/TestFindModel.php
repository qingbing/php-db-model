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

class TestFindModel extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \Exception
     */
    public function run()
    {
//        $criteria = new Criteria();
//        $stu = Stu::model()->find($criteria);
//        var_dump($stu);
//
//        $criteria = new Criteria();
//        $criteria->setLimit(3);
//        $stus = Stu::model()->findAll($criteria);
//        var_dump($stus);
//
//        $stu = Stu::model()->findByAttributes([
//            'class_id' => 2
//        ]);
//        var_dump($stu);
//
//        $stu = Stu::model()->findAllByAttributes([
//            'class_id' => 2
//        ]);
//        var_dump($stu);
//
//        $stu = Stu::model()->findByPk(2);
//        var_dump($stu);
//
//        $stus = Stu::model()->findAllByPks([1, 2, 3]);
//        var_dump($stus);

        $criteria = new Criteria();
        $criteria->addWhere('id>:id', [
            ':id' => 3,
        ]);
        $num = Stu::model()->count($criteria);
        var_dump($num);

        $num = Stu::model()->countByAttributes([
            'class_id' => 2,
        ]);
        var_dump($num);

    }
}