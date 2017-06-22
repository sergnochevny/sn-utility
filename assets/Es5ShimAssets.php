<?php

namespace ait\utilities\assets;

use yii\web\AssetBundle;


class Es5ShimAssets extends AssetBundle
{
    public $sourcePath = '@bower/es5-shim';
    public $js = [
        'es5-shim.min.js',
    ];
}