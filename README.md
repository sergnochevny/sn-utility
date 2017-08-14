Utilities Library
=================

* [Instalation](#installation)
* [Utilities elements:](#utilities-elements)
    - [AuditColumn](#auditcolumn)
    - [MobileDetect](#mobiledetect)
    - [Traits]()
        - [SaveContextTrait](#savecontexttrait)
    - [Helpers]()
        - [DecodePurifyHelper]()
        - [HttpHelper]()
        - [ImageHelper]()
        - [MIMEHelper]()
        - [MIMEReader]()
        - [Url]()
    - [Gehaviors]()
        - [ChildexistsBehavior]()
        - [GencodeBehavior]()
        - [GlobalAccessBehavior]()
        - [LastActionBehavior]()
        - [ManyToManyBehavior]()
        - [SluggableBehavior]()
        - [UploadBehavior]()
    - [Assets utilities]()
        - [AssetGzipConverter](#assetgzipconverter)
        - [ExtLibAsset](##extlibasset)
        - [Es5ShimAssets](##es5shimassets)
    - [Components]()
        - [AssetManager](#assetmanager)
        - [View](#view)

# Installation
--------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist ait/utilities "dev-master"
```

or add

```
"ait/utilities": "*"
```

to the require section of your `composer.json` file.


# Utilities elements
-----------------

## AuditColumn

### AuditColumn for GridView

```php
        ...
        [
            'class' => 'ait\utilities\AuditColumn',
            'visualAttributes' => [
                'status' => function ($model, $attr) {
                    return [$model->getAttributeLabel($attr), Order::getCurrentStatus($model->$attr)];
                },
                'created_at' => function ($model, $attr) {
                    return [$model->getAttributeLabel($attr), Yii::$app->formatter->asDate($model->$attr)];
                },
                'updated_at' => function ($model, $attr) {
                    return [$model->getAttributeLabel($attr), ($model->$attr > 1 ? Yii::$app->formatter->asDate($model->$attr) : 'No edits')];
                },
            ],
            'headerOptions' => [
                'width' => '40',
            ],
            'contentOptions' => [
                'width' => '40',
                'class' => 'text-center vertical-align-middle',
            ],
        ],
        ...
```

## MobileDetect
-------------------------------

### MobileDetect to detect mobile on backend:

```php
class AppAsset extends AssetBundle
{
    ...
    $md = new ait\utilities\MobileDetect();
    if ($md->isMobile()) $grid_params['pager'] = ['maxButtonCount' => 6];
    ...
}
```

## AssetGzipConverter
-------------------------------

### AssetGzipConverter to compress resources usage:

```php
        'assetManager' => [
            ...

            ...
            'converter' => [
                'class' => 'ait\utilities\assets\AssetGzipConverter'
            ]
        ],
```

## AssetManager
-------------------------------

### AssetManager to resources usage:

    * Additional parameters:
    - lazyPublish = true - When there are minified resources in the publication directory to prevent copying it
    - injectionCssScheme - Css injection sheme into HTML response
    - injectionJsScheme - Js injection sheme into HTML response

```php

        'assetManager' => [
            'class' => 'ait\utilities\components\AssetManager',
            'linkAssets' => true,
            'lazyPublish' => true,
            'injectionCssScheme' => AssetManager::SCHEME_INJECTION_INLINE,
            'injectionJsScheme' => AssetManager::SCHEME_INJECTION_ONLOAD,

            'beforeCopy' => function ($from, $to) {
                return !is_file($from) || !file_exists($to) || (filesize($from) !== filesize($to));
            },
            'excludeOptions' => ['only' => ['*.js', '*.css', '*.map']],
            'bundles' => [
                'yii\web\YiiAsset' => [
                    'sourcePath' => YII_ENV_DEV ? '@yii/assets' : '@webroot/js/assets',
                    'js' => [
                        YII_ENV_DEV ? 'yii.js' : 'yii.min.js',
                    ],
                ],
                'yii\jui\JuiAsset' => [
                    'js' => [
                        YII_ENV_DEV ? 'jquery-ui.js' : 'jquery-ui.min.js',
                    ],
                    'css' => [
                        YII_ENV_DEV ? 'themes/smoothness/jquery-ui.css' : 'themes/smoothness/jquery-ui.min.css',
                    ]
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [null]
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js' => [
                        YII_ENV_DEV ? 'js/bootstrap.js' : 'js/bootstrap.min.js',
                    ],
                ],
                'yii\widgets\PjaxAsset' => [
                    'sourcePath' => YII_ENV_DEV ? '@bower/yii2-pjax' : '@webroot/js/yii2-pjax',
                    'js' => [
                        YII_ENV_DEV ? 'jquery.pjax.js' : 'jquery.pjax.min.js',
                    ],
                ],
                'yii\web\JqueryAsset' => [
                    'js' => [
                        YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js',
                    ],
                ],
                'ait\authorize\JqueryPaymentAsset' => [
                    'js' => [
                        YII_ENV_DEV ? 'lib/jquery.payment.js' : 'lib/jquery.payment.min.js',
                    ]
                ],
                'yii\grid\GridViewAsset' => [
                    'sourcePath' => YII_ENV_DEV ? '@yii/assets' : '@webroot/js/assets',
                    'js' => [
                        YII_ENV_DEV ? 'yii.gridView.js' : 'yii.gridView.min.js',
                    ]
                ],
                'yii\widgets\ActiveFormAsset' => [
                    'sourcePath' => YII_ENV_DEV ? '@yii/assets' : '@webroot/js/assets',
                    'js' => [
                        YII_ENV_DEV ? 'yii.activeForm.js' : 'yii.activeForm.min.js',
                    ]
                ],
                'yii\validators\ValidationAsset' => [
                    'sourcePath' => YII_ENV_DEV ? '@yii/assets' : '@webroot/js/assets',
                    'js' => [
                        YII_ENV_DEV ? 'yii.validation.js' : 'yii.validation.min.js',
                    ]
                ],
                'yii\captcha\CaptchaAsset' => [
                    'sourcePath' => YII_ENV_DEV ? '@yii/assets' : '@webroot/js/assets',
                    'js' => [
                        YII_ENV_DEV ? 'yii.captcha.js' : 'yii.captcha.min.js',
                    ]
                ]
            ],
        ],

```

## View
-------------------------------

### View: implements of post loading all scripts & styles that will be registered by $this->register*** functions.

 * Prevented to append loaded scripts like yii.js. Add 'force' => true to force load script. see ex. below.

    example:
    ```php
        $this->registerJsFile('@web/js/modules/app/search-cities.min.js', ['depends' => [JqueryAsset::className()], 'force' => true]);
    ```

 * Register component in the app configuration in section components to use it:
```php

    ...
    'components' => [
        'view' => 'ait\utilities\components\View',
    ...

```

## ExtLibAsset
## Es5ShimAssets
-------------------------------

### Config AppAsset:

```php
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    ...

    ...
    public $depends = [
        ...
        'ait\assets\Es5ShimAssets',
        'ait\assets\ExtLibAsset',
        ...
    ];
}
```
