<?php

namespace sn\utility\controllers;

use yii\web\Controller;

class BaseController extends Controller
{
    /**
     * @param string $view
     * @param array $params
     * @return string
     */
    protected function smartRender($view, $params = [])
    {
        if (\Yii::$app->getRequest()->getIsAjax()) {
            return $this->renderAjax($view, $params);
        }

        return $this->render($view, $params);
    }
}