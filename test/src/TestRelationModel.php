<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-11
 * Version      :   1.0
 */

namespace Test;

use TestClass\Stu;
use TestClass\StuClass;
use TestCore\Tester;

class TestRelationModel extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \Exception
     */
    public function run()
    {
        // 查找数据并实例化成db模型
        $stu = Stu::model()->findByPk('4');
        var_dump($stu);
        // 获取数据模型信息
        $metaData = $stu->getMetaData();
        var_dump($metaData);

        // BELONGS_TO 使用示例
        $stuClass = $stu->stuClass;
        var_dump($stuClass);

        // STAT 使用示例
        $statCourse = $stu->statCourse;
        var_dump($statCourse);

        // HAS_MANY 使用示例
        $sourse = $stu->course;
        var_dump($sourse);

        //  HAS_ONE 使用示例
        $stuClass = StuClass::model()->findByPk(2);
        $master = $stuClass->master;
        var_dump($master);
    }
}