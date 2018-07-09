<?php

namespace sn\utility\assets;

use yii\web\AssetBundle;


class ExtLibAsset extends AssetBundle
{
    public $sourcePath = '@bower/ext-lib';
    public $js = [
        YII_ENV_DEV ? 'js/library.extends.js' : 'js/library.extends.min.js',
    ];
    public $css = [
        YII_ENV_DEV ? 'css/waitloader.css' : 'css/waitloader.min.css',
        YII_ENV_DEV ? 'css/confirm.css' : 'css/confirm.min.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
    ];
}