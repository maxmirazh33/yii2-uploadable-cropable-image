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
    public $sourcePath = '@maxmirazh33/image/assets';
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
        'maxmirazh33\image\Asset',
    ];
}
