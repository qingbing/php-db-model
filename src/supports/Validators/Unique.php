<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-13
 * Version      :   1.0
 */

namespace DbModelSupports\Validators;


use Abstracts\Validator;
use DbSupports\Builder\Criteria;

class Unique extends Validator
{
    /* @var string 自定义的错误消息："{attribute}"可以替代成属性的"label" */
    public $message = '"{attribute}"."{value}"已经存在了';
    /* @var string 是否区分大小写 */
    public $caseSensitive = true;
    /* @var boolean 属性值是否允许为空 */
    public $allowEmpty = true;

    /**
     * 通过当前规则验证属性，如果有验证不通过的情况，将通过 model 的 addError 方法添加错误信息
     * @param \Abstracts\DbModel $object
     * @param string $attribute
     * @throws \Exception
     */
    protected function validateAttribute($object, $attribute)
    {
        $value = $object->{$attribute};
        if ($this->isEmpty($value)) {
            $this->validateEmpty($object, $attribute);
            return;
        }
        $criteria = new Criteria();
        if (!$object->getIsNewRecord()) {
            $bk = ":{$object->primaryKey()}";
            $criteria->addWhere("`{$object->primaryKey()}`!={$bk}", [
                $bk => $object->getPrimaryKey()
            ]);
        }
        $bk = ":{$attribute}";
        if ($this->caseSensitive) {
            $criteria->addWhere("`{$attribute}`={$bk}");
        } else {
            $criteria->addWhere("LOWER(`{$attribute}`)=LOWER({$bk})");
        }
        $criteria->addParam($bk, $value);
        $record = $object->find($criteria);
        if (null !== $record) {
            $this->addError($object, $attribute, $this->message, [
                '{value}' => $value,
            ]);
        }
    }
}