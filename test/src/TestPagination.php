<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2019-03-03
 * Version      :   1.0
 */

namespace Test;


use DbSupports\Builder\Criteria;
use TestClass\Stu;
use TestCore\Tester;

class TestPagination extends Tester
{

    /**
     * 执行函数
     * @throws \Exception
     */
    public function run()
    {
        // 通过 criteria 查询单个数据模型
        $criteria = new Criteria();
        $model = new Stu();
        $rs = $model->pagination($criteria, true, 5, 1);
        var_dump($rs);
    }
}