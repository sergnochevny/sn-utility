<?php

namespace ait\utilities\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\validators\ExistValidator;

/**
 * Class ChildexistsBehavior
 * @package ait\utilities\behaviors
 *
 *    public function behaviors()
 *   {
 *      return [
 *          ['class' => TimestampBehavior::className()],
 *          [
 *              'class' => ChildexistsBehavior::className(),
 *              'parent' => 'id',
 *              'model_class' => Customers::className(),
 *              'child' => 'user_id',
 *              'message' => 'Deleting is not possible - there are dependent customers.',
 *          ],
 *      ];
 *   }
 *
 */
class ChildexistsBehavior extends Behavior
{

    private $beforeDelAction = 'beforeDelAction';
    public $parent;
    public $child;
    public $message;
    public $model_class;

    public function init()
    {
        parent::init();

        if (is_null($this->parent) || is_null($this->child) || is_null($this->message)) {
            throw new InvalidConfigException('parent, child & message  parameters must be identity');
        }
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => $this->beforeDelAction,
        ];

    }

    public function beforeDelAction($event)
    {
        $model = clone $this->owner;
        $validator = Yii::createObject(ExistValidator::className());
        $validator->targetAttribute = [$this->parent => $this->child];
        if (!is_null($this->model_class)) {
            $validator->targetClass = new $this->model_class;
        }
        $validator->validateAttribute($model, $this->parent);
        $res = $model->hasErrors();
        $event->handled = !$res;
        $event->isValid = $res;

        if (!$res) {
            Yii::$app->session->setFlash('alert', [
                'body' => $this->message,
                'options' => ['class' => 'alert-danger'],
            ]);
        }

        return $res;

    }
}