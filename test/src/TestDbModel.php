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

class TestDbModel extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run()
    {
        $stuModel = new Stu();

        var_dump($stuModel);
//        $stuModel = Stu::model();
//
//        var_dump($stuModel);


    }
}