<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-11
 * Version      :   1.0
 */

namespace TestClass;

use DbModel;

class StuCourse extends DbModel
{
    /**
     * 显示定义数据表名称，和类名相同，可以不用显示定义，但是建议都定义下
     * @return string
     */
    public function tableName()
    {
        return "{{stu_course}}";
    }

    /**
     * 和模型关联关系
     * @return array
     */
    public function relations()
    {
        return [
            'course' => [self::BELONGS_TO, '\TestClass\Stu', 'stu_id'],
        ];
    }
}