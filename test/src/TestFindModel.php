<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-11
 * Version      :   1.0
 */

namespace Test;

use DbSupports\Builder\Criteria;
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
        // 通过 criteria 查询单个数据模型
        $criteria = new Criteria();
        $stu = Stu::model()->find($criteria);
        var_dump($stu);

        // 通过 criteria 查询所有数据模型
        $criteria = new Criteria();
        $criteria->setLimit(3);
        $stus = Stu::model()->findAll($criteria);
        var_dump($stus);

        // 通过属性查询单个数据模型
        $stu = Stu::model()->findByAttributes([
            'class_id' => 3
        ]);
        var_dump($stu);

        // 通过属性查询多个数据模型
        $stu = Stu::model()->findAllByAttributes([
            'class_id' => 2
        ]);
        var_dump($stu);

        // 通过主键查询单个数据模型
        $stu = Stu::model()->findByPk(2);
        var_dump($stu);

        // 通过主键查询多个数据模型
        $stus = Stu::model()->findAllByPks([1, 2, 3]);
        var_dump($stus);

        // 通过 criteria 统计符合条件的数量
        $criteria = new Criteria();
        $criteria->addWhere('id>:id', [
            ':id' => 3,
        ]);
        $num = Stu::model()->count($criteria);
        var_dump($num);

        // 通过数组属性统计符合条件的数量
        $num = Stu::model()->countByAttributes([
            'class_id' => 2,
        ]);
        var_dump($num);

        // 查询是否有符合条件的记录
        $criteria = new Criteria();
        $criteria->addWhere('id>:id', [
            ':id' => 3,
        ]);
        $isExists = Stu::model()->exists($criteria);
        var_dump($isExists);

        // 查询是否有符合条件的记录
        $num = Stu::model()->existByAttributes([
            'class_id' => 2,
        ]);
        var_dump($num);
    }
}