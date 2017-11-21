<?php

namespace ait\utility\validators\assets;

use yii\web\AssetBundle;

class DependValidationAsset extends AssetBundle
{
    public $sourcePath = '@ait/utility/validators/assets';
    public $js = [
        'depend.validation.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\validators\ValidationAsset'
    ];
}
