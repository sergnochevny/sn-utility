<?php

namespace sn\utility\validators\assets;

use yii\web\AssetBundle;

class DependValidationAsset extends AssetBundle
{
    public $sourcePath = '@sn/utility/validators/assets';
    public $js = [
        'depend.validation.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\validators\ValidationAsset'
    ];
}
