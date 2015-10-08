<?php
namespace maxmirazh33\image;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\InputWidget;
use Yii;

/**
 * Class for uploadable and cropable image widget
 *
 * Usage:
 * ```
 * ...
 * echo $form->field($model, 'image')->widget('maxmirazh33\image\Widget');
 * ...
 * ```
 */
class Widget extends InputWidget
{
    /**
     * @var bool need crop
     */
    private $crop = false;
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
        Asset::register($this->getView());

        foreach ($this->model->behaviors as $b) {
            if ($b instanceof Behavior) {
                foreach ($b->attributes as $attr => $options) {
                    Behavior::ensureAttribute($attr, $options);
                    if ($attr == $this->attribute) {
                        if ($b->needCrop($attr)) {
                            $this->crop = true;
                            CropAsset::register($this->getView());
                            $this->jcropSettings = array_merge($this->jcropDefaultSettings, $this->jcropSettings);
                            $this->jcropSettings['onSelect'] = new JsExpression('function (c) {
                                setCoords("' . $this->getSelector() . '", c);
                            }');
                            if (isset($options['width'], $options['height'])) {
                                $this->jcropSettings['aspectRatio'] = $options['width'] / $options['height'];
                            }
                        }
                        break;
                    }
                }
                break;
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
        $object = new \ReflectionClass($this->model);
        return mb_strtolower($object->getShortName()) . '-' . $this->attribute;
    }
}
