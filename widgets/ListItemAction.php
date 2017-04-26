<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ait\utilities\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Console;
use yii\helpers\Html;
use yii\helpers\Url;

class ListItemAction extends Widget
{
    public $model;

    public $key;

    public $template = '{preview} {update} {delete}';
    /**
     * @var array button rendering callbacks. The array keys are the button names (without curly brackets),
     * and the values are the corresponding button rendering callbacks. The callbacks should use the following
     * signature:
     *
     * ```php
     * function ($url, $model, $key) {
     *     // return the button HTML code
     * }
     * ```
     *
     * where `$url` is the URL that the column creates for the button, `$model` is the model object
     * being rendered for the current row, and `$key` is the key of the model in the data provider array.
     *
     * You can add further conditions to the button, for example only display it, when the model is
     * editable (here assuming you have a status field that indicates that):
     *
     * ```php
     * [
     *     'update' => function ($url, $model, $key) {
     *         return $model->status === 'editable' ? Html::a('Update', $url) : '';
     *     },
     * ],
     * ```
     */
    public $buttons = [];
    /** @var array visibility conditions for each button. The array keys are the button names (without curly brackets),
     * and the values are the boolean true/false or the anonymous function. When the button name is not specified in
     * this array it will be shown by default.
     * The callbacks must use the following signature:
     *
     * ```php
     * function ($model, $key) {
     *     return $model->status === 'editable';
     * }
     * ```
     *
     * Or you can pass a boolean value:
     *
     * ```php
     * [
     *     'update' => \Yii::$app->user->can('update'),
     * ],
     * ```
     *
     */
    public $visibleButtons = [];
    /**
     * @var callable a callback that creates a button URL using the specified model information.
     * The signature of the callback should be the same as that of [[createUrl()]]
     * Since 2.0.10 it can accept additional parameter, which refers to the column instance itself:
     *
     * ```php
     * function (string $action, mixed $model, mixed $key, ActionColumn $this) {
     *     //return string;
     * }
     * ```
     *
     * If this property is not set, button URLs will be created using [[createUrl()]].
     */
    public $urlCreator;
    /**
     * @var array html options to be applied to the [[initDefaultButton()|default button]].
     */
    public $buttonOptions = [];

    public $options = [];

    protected function initDefaultButtons()
    {
        $this->initDefaultButton('preview', 'file-text-o');
        $this->initDefaultButton('update', 'pencil');
        $this->initDefaultButton('delete', 'trash', [
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-method' => 'post', 'data-pjax' => true,
        ]);
    }

    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                $title = Yii::t('yii', ucfirst($name));
                $options = array_merge([
                    'class' => '',
                    'title' => $title,
                    'aria-label' => $title,
                ], $this->options);
                $options['class'] = (!empty($options['class']) ? $options['class'] . ' ' : '') . $name;
                $icon = Html::tag('i', '', ['class' => "fa fa-$iconName"]);
                return Html::tag('div', Html::a($icon, $url, array_merge($this->buttonOptions, $additionalOptions)), $options);
            };
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initDefaultButtons();
    }

    public function createUrl($action, $model, $key)
    {
        if (is_callable($this->urlCreator)) {
            return call_user_func($this->urlCreator, $action, $model, $key, $this);
        } else {
            if ($key instanceof \Closure) $key = call_user_func($action, $model, $this);
            $controller = $this->view->context;
            $params = is_array($key) ? $key : (empty($key) ? ['id' => (string)$model->primaryKey] : ['id' => (string)$key]);
            $params[0] = $controller ? $controller->id . '/' . $action : $action;

            return Url::toRoute($params);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) {
            $name = $matches[1];

            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure
                    ? call_user_func($this->visibleButtons[$name], $this->model, $this->key)
                    : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }

            if ($isVisible && isset($this->buttons[$name])) {
                $url = $this->createUrl($name, $this->model, $this->key);
                return call_user_func($this->buttons[$name], $url, $this->model, $this->key);
            } else {
                return '';
            }
        }, $this->template);
    }

}
