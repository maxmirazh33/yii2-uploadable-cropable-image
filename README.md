Yii2 uploadable and cropable image
==================================
Yii2 extension for upload and crop images

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist maxmirazh33/yii2-uploadable-cropable-image "*"
```

or add

```
"maxmirazh33/yii2-uploadable-cropable-image": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

In your model:
```php
public function behaviors()
    {
        return [
            'uploadBehavior' => [
                'class' => \maxmirazh33\image\Behavior::className(),
                'attributes' => [
                    'image' => [
                        'width' => 600,
                        'height' => 300,
                        'crop' => true,
                        'thumbnails' => [
                            'mini' => [
                                'width' => 100,
                            ],
                        ],
                    ],
                ],
            ],
            //other behaviors
        ];
    }
```

In your view file:
```php
echo $form->field($model, 'image')->widget('maxmirazh33\image\Widget');
```
