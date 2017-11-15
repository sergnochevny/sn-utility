<?php

namespace ait\utility\helpers;

use Yii;
use yii\base\InvalidParamException;
use yii\helpers\BaseUrl;

class Url extends BaseUrl
{
    /**
     * @inheritdoc
     */
    public static function remember($url = '', $name = null)
    {
        $url = static::to($url);

        if ($name === null) {
            $backUrl = Yii::$app->getUser()->getBackUrl();
            if ($backUrl !== $url) {
                Yii::$app->getUser()->setReturnUrl($backUrl);
                Yii::$app->getUser()->setBackUrl($url);
            }
        } else {
            Yii::$app->getSession()->set($name, $url);
        }
    }

    /**
     * @inheritdoc
     */
    public static function back($name = null)
    {
        if ($name === null) {
            return Yii::$app->getUser()->getBackUrl();
        } else {
            return Yii::$app->getSession()->get($name);
        }
    }

    public static function to($url = '', $scheme = true)
    {
        return parent::to($url, $scheme);
    }

    /**
     * @inheritdoc
     */
    protected static function normalizeRoute($route)
    {
        $route = Yii::getAlias((string)$route);
        if (strncmp($route, '/', 1) === 0) {
            // absolute route
            return ltrim($route, '/');
        }

        // relative route
        if (Yii::$app->controller === null) {
            throw new InvalidParamException("Unable to resolve the relative route: $route. No active controller is available.");
        }

        if (strpos($route, '/') === false) {
            // empty or an action ID
            return $route === '' ? Yii::$app->controller->getUniqueId() : Yii::$app->controller->getUniqueId() . '/' . $route;
        } else {
            // relative to module
            return ltrim(Yii::$app->controller->module->getUniqueId() . '/' . $route, '/');
        }
    }


}
