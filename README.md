Yii2 uploadable and cropable image
==================================
Yii2 extension for upload and crop images

[![Latest Stable Version](https://poser.pugx.org/maxmirazh33/yii2-uploadable-cropable-image/v/stable.svg)](https://packagist.org/packages/maxmirazh33/yii2-uploadable-cropable-image)
[![Total Downloads](https://poser.pugx.org/maxmirazh33/yii2-uploadable-cropable-image/downloads.svg)](https://packagist.org/packages/maxmirazh33/yii2-uploadable-cropable-image)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/maxmirazh33/yii2-uploadable-cropable-image/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/maxmirazh33/yii2-uploadable-cropable-image/?branch=master)
[![Code Climate](https://codeclimate.com/github/maxmirazh33/yii2-uploadable-cropable-image/badges/gpa.svg)](https://codeclimate.com/github/maxmirazh33/yii2-uploadable-cropable-image)
[![Latest Unstable Version](https://poser.pugx.org/maxmirazh33/yii2-uploadable-cropable-image/v/unstable.svg)](https://packagist.org/packages/maxmirazh33/yii2-uploadable-cropable-image)
[![License](https://poser.pugx.org/maxmirazh33/yii2-uploadable-cropable-image/license.svg)](https://packagist.org/packages/maxmirazh33/yii2-uploadable-cropable-image)
[![Dependency Status](https://www.versioneye.com/user/projects/54d1d39f3ca08473b4000156/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54d1d39f3ca08473b4000156)
[![Build Status](https://scrutinizer-ci.com/g/maxmirazh33/yii2-uploadable-cropable-image/badges/build.png?b=master)](https://scrutinizer-ci.com/g/maxmirazh33/yii2-uploadable-cropable-image/build-status/master)

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

Don't add rules in your model for used attribute. Validator added automatically.

In your view file:
```php
echo $form->field($model, 'image')->widget('maxmirazh33\image\Widget');
```
