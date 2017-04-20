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