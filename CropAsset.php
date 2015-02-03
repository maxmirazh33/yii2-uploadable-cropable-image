<?php

namespace maxmirazh33\image;

use yii\web\AssetBundle;

/**
 * Crop asset bundle.
 */
class CropAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = 'assets/';

    /**
     * @inheritdoc
     */
    public $css = [
        'jcrop/css/jquery.Jcrop.min.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'jcrop/js/jquery.Jcrop.min.js',
        'js/jcrop.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
    ];
}
