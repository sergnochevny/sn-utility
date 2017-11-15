<?php

namespace ait\utility\behaviors;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;
use yii\validators\UniqueValidator;


class GencodeBehavior extends AttributeBehavior
{
    public $codeAttribute = 'code';
    public $attribute;
    public $value;
    public $ensureUnique = true;

    public $immutable = true;

    public $uniqueValidator = [];
    public $uniqueCodeGenerator;
    public $codelength = 8;

    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [BaseActiveRecord::EVENT_BEFORE_VALIDATE => $this->codeAttribute];
        }

    }

    /**
     * @inheritdoc
     */

    protected function getValue($event)
    {
        if ($this->value !== null) {
            $code = parent::getValue($event);
        } else {
            if ($this->isNewNeeded()) {
                $parts = [];
                if ($this->attribute !== null) {
                    foreach ((array)$this->attribute as $attribute) {
                        $parts[] = $this->owner->{$attribute};
                    }
                }
                $code = $this->generateCode($parts);
            } else {
                return $this->owner->{$this->codeAttribute};
            }
        }

        return $this->ensureUnique ? $this->makeUnique($code) : $code;
    }

    protected function isNewNeeded()
    {
        if (empty($this->owner->{$this->codeAttribute})) {
            return true;
        }

        if ($this->immutable) {
            return false;
        }

        foreach ((array)$this->attribute as $attribute) {
            if ($this->owner->isAttributeChanged($attribute)) {
                return true;
            }
        }

        return false;
    }

    protected function generateCode($parts)
    {
        $bytes = Yii::$app->security->generateRandomKey($this->codelength);
        return strtolower(strtr(substr(base64_encode($bytes), 0, $this->codelength), '+/', 'a2'));
    }

    protected function makeUnique($code)
    {
        $uniqueCode = $code;
        while (!$this->validateCode($uniqueCode)) {
            $uniqueCode = $this->generateUniqueCode();
        }
        return $uniqueCode;
    }

    protected function validateCode($code)
    {
        $validator = Yii::createObject(array_merge(
            [
                'class' => UniqueValidator::className(),
            ],
            $this->uniqueValidator
        ));

        $model = clone $this->owner;
        $model->clearErrors();
        $model->{$this->codeAttribute} = $code;

        $validator->validateAttribute($model, $this->codeAttribute);
        return !$model->hasErrors();
    }

    protected function generateUniqueCode()
    {
        if (is_callable($this->uniqueCodeGenerator)) {
            return call_user_func($this->uniqueCodeGenerator, $this->owner, $this->attribute);
        }

        $parts = [];
        foreach ((array)$this->attribute as $attribute) {
            $parts[] = $this->owner->{$attribute};
        }

        $code = $this->generateCode($parts);

        return $code;
    }

}