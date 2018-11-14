<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-13
 * Version      :   1.0
 */

namespace TestClass;

use DbModel;

class StuClass extends DbModel
{
    /**
     * 显示定义数据表名称，和类名相同，可以不用显示定义，但是建议都定义下
     * @return string
     */
    public function tableName()
    {
        return "{{stu_class}}";
    }

    /**
     * 和模型关联关系
     * @return array
     */
    public function relations()
    {
        return [
            'master' => [self::HAS_ONE, '\TestClass\Stu', 'stu_id', 'condition' => '`is_master`=:is_master', 'params' => [':is_master' => 1]],
            'stu' => [self::HAS_MANY, '\TestClass\Stu', 'stu_id'],
        ];
    }
}