<?php

namespace maxmirazh33\image;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\InputWidget;
use Yii;

/**
 * Class for uploadable and cropable image widget
 *
 * Usage:
 * ```
 * ...
 * echo $form->field($model, 'image')->widget('maxmirazh33\image\Widget', [
 *     'crop' => true,
 *     'width' => 600,
 *     'height' => 300,
 * ]);
 * ...
 * ```
 */
class Widget extends InputWidget
{
    /**
     * @var boolean enable/disable crop
     */
    public $crop = true;

    /**
     * @var integer image width
     */
    public $width;

    /**
     * @var integer image height
     */
    public $height;

    /**
     * @var array JCrop settings
     */
    public $jcropSettings = [];

    /**
     * @var array default JCrop settings
     */
    private $jcropDefaultSettings = [
        'bgColor' => '#ffffff',
        'minSize' => [100, 100],
        'keySupport' => false, // Important param to hide jCrop radio button.
        'setSelect' => [0, 0, 9999, 9999],
        'boxWidth' => 568,
        'boxHeight' => 400,
        'onSelect' => 'setCoords',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->hasModel() && $this->name === null) {
            throw new InvalidConfigException("'model' and 'attribute' properties must be specified.");
        }
        parent::init();

        $this->registerTranslations();

        if ($this->crop) {
            CropAsset::register($this->getView());
            $this->jcropSettings = ArrayHelper::merge($this->jcropSettings, $this->jcropDefaultSettings);
            if (isset($this->width) && isset($this->height)) {
                $this->jcropSettings['aspectRatio'] = $this->width / $this->height;
            }
        }
    }

    /**
     * Register widget translations.
     */
    public function registerTranslations()
    {
        if (!isset(Yii::$app->i18n->translations['maxmirazh33/image']) && !isset(Yii::$app->i18n->translations['maxmirazh33/*'])) {
            Yii::$app->i18n->translations['maxmirazh33/image'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@maxmirazh33/image/messages',
                'fileMap' => [
                    'maxmirazh33/image' => 'image.php'
                ],
                'forceTranslation' => true
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render(
            'view',
            [
                'selector' => $this->getSelector(),
                'model' => $this->model,
                'attribute' => $this->attribute,
                'crop' => $this->crop,
                'jcropSettings' => $this->jcropSettings,
            ]
        );
    }


    /**
     * @return string Widget selector
     */
    public function getSelector()
    {
        return get_called_class($this->model) . '-' . $this->attribute;
    }
}