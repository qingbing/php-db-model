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

class TestRelationModel extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \Exception
     */
    public function run()
    {
        $stu = Stu::model()->findByPk('4');
//        var_dump($stu);
//        $metaData = $stu->getMetaData();
//        var_dump($metaData);
//        $stuClass = $stu->stuClass;
//        var_dump($stuClass);
        $statCourse = $stu->statCourse;
        var_dump($statCourse);
        $sourse = $stu->course;
        var_dump($sourse);
    }
}