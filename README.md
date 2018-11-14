# php-model
## 描述
db-model 的相关操作，定义了包含数据表关联关系的数据表操作ar模型

## 注意事项
 - 继承自php-model的 Model 类，包含有 php-model 的所有相关操作
 - 提供对数据表的对应关系，save(insert和update)、delete、find等操作
 - 增加了model的模型唯一性验证 : \DbModel\Validators\Unique
 - 数据对象之间包含有四种关联关系
   - BELONGS_TO : 属于
   - HAS_MANY : 拥有（多个）
   - HAS_ONE : 拥有（有且只有一个）
   - STAT : 统计符合条件的个数


## 使用方法
### 0、基础模型
```php

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
```
### 1、模型基础操作
```php
        // 新增模型数据
        $model = new Stu();
        // 设置字段属性
        $model->setAttributes([
            'name' => 'insertName',
            'class_id' => '4',
            'sex' => '1',
        ]);
        // 获取字段数据
        var_dump($model->name);
        var_dump($model->getAttribute('name'));
        // 获取所有字段数据
        var_dump($model->getAttributes());
```
### 2、模型新增操作
```php
        // 新增模型数据
        $model = new Stu();

        // 设置模型数据
        $model->setAttributes([
            'name' => 'insertName',
            'class_id' => '4',
            'sex' => '1',
        ]);

        // 保存判断
        if ($model->save()) {
            var_dump('save success');
            var_dump($model);
        } else {
            var_dump($model->getErrors());
        }
```
### 3、模型更新操作
```php
        // 通过 Criteria 更新数据
        $criteria = new Criteria();
        $criteria->addWhere('`id`>=:minId', [
            ':minId' => 7
        ]);
        $num = Stu::model()->updateAll([
            'name' => 'updateAll',
        ], $criteria);
        var_dump($num);

        // 根据属性修改数据
        $num = Stu::model()->updateAllByAttributes([
            'name' => 'updateAllByAttributes',
        ], [
            'id' => 7
        ]);
        var_dump($num);

        // 根据主键更新记录
        $num = Stu::model()->updateByPk(8, [
            'name' => 'updateByPk',
        ]);
        var_dump($num);

        // 模型查询
        $model = Stu::model()->findByPk('9');
        $model->setAttributes([
            'name' => 'updateSave',
        ]);
        if ($model->save()) {
            var_dump('save success');
        } else {
            var_dump($model->getErrors());
        }
```
### 4、模型删除操作
```php
        // 通过 Criteria 删除数据
        $criteria = new Criteria();
        $criteria->addWhere('`id`>=:minId', [
            ':minId' => 13
        ]);
        $num = Stu::model()->deleteAll($criteria);
        var_dump($num);

        // 删除对应属性的记录
        $num = Stu::model()->deleteAllByAttributes([
            'id' => 12
        ]);
        var_dump($num);

        // 删除对应主键的记录
        $num = Stu::model()->deleteByPk(11);
        var_dump($num);

        // 通过模型删除记录
        $stu = Stu::model()->findByPk(10);
        if ($stu) {
            if ($stu->delete()) {
                var_dump('delete success');
            } else {
                var_dump('delete failure');
            }
        } else {
            var_dump('record has been deleted');
        }
```
### 5、模型查找操作
```php
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
```
### 6、模型关联操作
```php
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
```
### 7、模型唯一性定义验证
```php
        $model = new StuClass();

        $model->setAttributes([
            'name' => '二年级',
        ]);

        // 保存判断
        if ($model->save()) {
            var_dump('save success');
            var_dump($model);
        } else {
            var_dump($model->getErrors());
        }
```
## ====== 异常代码集合 ======

异常代码格式：1012 - XXX - XX （组件编号 - 文件编号 - 代码内异常）
```
 - 101200101 : 模型不能重复执行添加操作
 - 101200102 : 新增模型不能使用更新操作
 - 101200103 : 新增模型不能使用删除操作
 
 - 101200201 : 模型"{class}"的数据表"{tableName}"不存在
 - 101200202 : "{class}"中存在无效的关联关系配置"{relation}"
```
