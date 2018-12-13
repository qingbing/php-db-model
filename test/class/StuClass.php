<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-11-13
 * Version      :   1.0
 */

namespace TestClass;


use Abstracts\DbModel;

/**
 * Class StuClass
 * @package TestClass
 *
 * @property-read \TestClass\Stu master
 * @property-read \TestClass\Stu[] stu
 */
class StuClass extends DbModel
{
    protected $cachingDuration = 0;

    /**
     * 显示定义数据表名称，和类名相同，可以不用显示定义，但是建议都定义下
     * @return string
     */
    public function tableName()
    {
        return "{{class}}";
    }

    /**
     * 定义并返回模型属性的验证规则
     * @return array
     */
    public function rules()
    {
        return [
            ['name', 'string'],
            ['name', self::UNIQUE], // 模型唯一性验证
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
            'master' => [self::HAS_ONE, '\TestClass\Stu', 'class_id', 'condition' => '`is_master`=:is_master', 'params' => [':is_master' => 1]],
            'stu' => [self::HAS_MANY, '\TestClass\Stu', 'class_id'],
        ];
    }

    /**
     * 获取属性标签，该属性在必要时需要被实例类重写
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增ID',
            'name' => '年级',
        ];
    }
}