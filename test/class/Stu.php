<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-11
 * Version      :   1.0
 */

namespace TestClass;

use DbModel;

class Stu extends DbModel
{
    /* @var int 开启缓存时缓存的时间（秒） */
    protected $cachingDuration = 0;

    /**
     * 显示定义数据表名称，和类名相同，可以不用显示定义，但是建议都定义下
     * @return string
     */
    public function tableName()
    {
        return "{{stu}}";
    }

    /**
     * 定义并返回模型属性的验证规则
     * @return array
     */
    public function rules()
    {
        return [
            ['class_id, name, sex, is_master', 'string'],
            ['id', 'safe'],
        ];
    }

    /**
     * 和模型关联关系
     * @return array
     */
    public function relations()
    {
        return [
            'stuClass' => [self::BELONGS_TO, '\TestClass\StuClass', 'class_id'],
            'course' => [self::HAS_MANY, '\TestClass\StuCourse', 'stu_id'],
            'statCourse' => [self::STAT, '\TestClass\StuCourse', 'stu_id'],
        ];
    }
}