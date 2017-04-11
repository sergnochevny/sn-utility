<?php

namespace ait\utilities;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use backend\assets\AuditColumnAssetBundle;

class AuditColumn extends \yii\grid\DataColumn
{
    public $visualAttributes = null;

    /**
     *
     */
    public function init()
    {
        $this->visualAttributes = (isset($this->visualAttributes))?$this->visualAttributes:[];
        $this->label = $this->label ?: ' ';
        $this->content = [$this, 'makeAuditCellContent'];
        AuditColumnAssetBundle::register($this->grid->view);
    }

    /**
     * @param $model
     * @return string
     */
    protected function makeAuditCellContent($model)
    {
        return $this->makeAuditPopoverElement($this->getAuditValues($model));
    }

    /**
     * @param $values
     * @return mixed
     */
    protected function makePopoverContent($values)
    {
        $formatter = function ($pair) {
            return Html::tag('div', Html::tag('strong', Html::encode($pair[0]), ['class'=>'aud-title']) . '&nbsp;' . Html::encode($pair[1]));
        };

        $appender = function ($accumulator, $values) {
            return $accumulator . $values;
        };

        return array_reduce(array_map($formatter, $values), $appender, '');
    }

    /**
     * @param $value
     * @return string
     */
    protected function makeAuditPopoverElement($value)
    {
        return Html::tag('span', '', [
            'title' => 'Info',
            'class' => 'audit-toggler fa fa-info-circle vertical-align-middle',
            'style' => 'cursor: pointer',
            'data-toggle' => 'popover',
            'data-html' => 'true',
            'data-title' => 'Info',
            'data-content' => $this->makePopoverContent($value),
        ]);
    }

    /**
     * @param $model Model
     * @return array
     */
    protected function getAuditValues($model)
    {
        $res = [
            [$model->getAttributeLabel('created_at'),
                Yii::$app->formatter->asDate($model->created_at)],
            [$model->getAttributeLabel('updated_at'),
                ($model->updated_at > 1 ? Yii::$app->formatter->asDate($model->updated_at) : 'No edits')],
        ];
        $attributes = array_intersect_key($this->visualAttributes, $model->attributes);
        if(count($attributes)){
            $res = [];
            foreach($attributes as $attribute => $value){
                if($value instanceof \Closure){
                    $res[] = $value($model, $attribute);
                } else {
                    $res[] = [$model->getAttributeLabel($attribute), Yii::$app->formatter->format($model->$attribute, $value)];
                }
            }
        }

        return $res;
    }

}