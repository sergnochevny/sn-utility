<?php

namespace ait\utilities\validators;

use ait\utilities\validators\assets\DependValidationAsset;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\Html;
use yii\validators\Validator;

class DependRequiredValidator extends Validator
{
    /**
     * @var bool whether to skip this validator if the value being validated is empty.
     */
    public $skipOnEmpty = false;

    /**
     * @var string the user-defined error message. It may contain the following placeholders which
     * will be replaced accordingly by the validator:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     * - `{requiredValue}`: the value of [[requiredValue]]
     */
    public $message;

    public $dependedAttributes;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if(empty($this->dependedAttributes)) throw new InvalidParamException('dependedAttributes parameter cannot be blank');
        $this->dependedAttributes = (array) $this->dependedAttributes;
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} cannot be blank.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $dependedValidator = Validator::createValidator('required', $model, $this->dependedAttributes);
        foreach ($this->dependedAttributes as $attribute){
             if (!$dependedValidator->validate($model->{$attribute})){
                 $this->addError($model, $attribute, $this->message);
             };
        }
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        DependValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.dependedrequired($form, value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * @inheritdoc
     */
    public function getClientOptions($model, $attribute)
    {
        $options = [];
        $depended = [];
        foreach($this->dependedAttributes as $dependedAttribute){
            $depended[] = Html::getInputName($model, $dependedAttribute);
        }
        $options['message'] = Yii::$app->getI18n()->format($this->message, [
            'attribute' => $model->getAttributeLabel($attribute),
        ], Yii::$app->language);
        $options['depended'] = $depended;
        return $options;
    }

}
