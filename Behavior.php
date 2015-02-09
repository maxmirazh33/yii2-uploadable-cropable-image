<?php

namespace maxmirazh33\image;

use Imagine\Image\Box;
use Imagine\Image\Point;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\imagine\Image;
use yii\web\UploadedFile;
use Yii;

/**
 * Class model behavior for uploadable and cropable image
 *
 * Usage in your model:
 * ```
 * ...
 * public function behaviors()
 * {
 *     return [
 *         [
 *              'class' => \maxmirazh33\image\Behavior::className(),
 *              'savePathAlias' => '@web/images/',
 *              'urlPrefix' => '/images/',
 *              'crop' => true,
 *              'attributes' => [
 *                  'avatar' => [
 *                      'savePathAlias' => '@web/images/avatars/',
 *                      'urlPrefix' => '/images/avatars/',
 *                      'width' => 100,
 *                      'height' => 100,
 *                  ],
 *                  'logo' => [
 *                      'crop' => false,
 *                      'thumbnails' => [
 *                          'mini' => [
 *                              'width' => 50,
 *                          ],
 *                      ],
 *                  ],
 *              ],
 *         ],
 *     //other behaviors
 *     ];
 * }
 * ...
 * ```
 */
class Behavior extends \yii\base\Behavior
{
    /**
     * @var array list of attribute as $attributeName => $options. Options:
     *  $width @see maxmirazh33\image\Behavior $width
     *  $height @see maxmirazh33\image\Behavior $height
     *  $savePathAlias @see maxmirazh33\image\Behavior $savePathAlias
     *  $crop @see maxmirazh33\image\Behavior $crop
     *  $urlPrefix @see maxmirazh33\image\Behavior $urlPrefix
     *  $thumbnails - array of thumbnails as $prefix => $options. Options:
     *          $width @see maxmirazh33\image\Behavior $width
     *          $height @see maxmirazh33\image\Behavior $height
     *          $savePathAlias @see maxmirazh33\image\Behavior $savePathAlias
     *          $urlPrefix @see maxmirazh33\image\Behavior $urlPrefix
     */
    public $attributes = [];
    /**
     * @var string. Default @frontend/web/images/className or @app/web/images/className
     */
    public $savePathAlias;
    /**
     * @var bool enable/disable crop.
     */
    public $crop = true;
    /**
     * @var string part of url for image without hostname. Default '/images/className/'
     */
    public $urlPrefix;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        /* @var $model ActiveRecord */
        $model = $this->owner;
        foreach ($this->attributes as $attr => $options) {
            $this->ensureAttributes($attr, $options);
            if ($file = UploadedFile::getInstance($model, $attr)) {
                $model->{$attr} = $file;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        /* @var $model ActiveRecord */
        $model = $this->owner;
        foreach ($this->attributes as $attr => $options) {
            $this->ensureAttributes($attr, $options);
            if ($file = UploadedFile::getInstance($model, $attr)) {
                $this->createDirIfNotExists($attr);
                if (!$model->isNewRecord) {
                    $this->deleteFiles($attr);
                }
                $fileName = uniqid() . '.' . $file->extension;
                if ($this->needCrop($attr)) {
                    $coords = $this->getCoords($attr);
                    if ($coords === false) {
                        throw new InvalidCallException();
                    }
                    if (isset($options['width']) && !isset($options['height'])) {
                        $width = $options['width'];
                        $height = $options['width'] * $coords['h'] / $coords['w'];
                    } elseif (!isset($options['width']) && isset($options['height'])) {
                        $width = $options['height'] * $coords['w'] / $coords['h'];
                        $height = $options['height'];
                    } elseif (isset($options['width']) && isset($options['height'])) {
                        $width = $options['width'];
                        $height = $options['height'];
                    } else {
                        $width = $coords['w'];
                        $height = $coords['h'];
                    }
                    Image::crop($file->tempName, $coords['w'], $coords['h'], [$coords['x'], $coords['y']])
                        ->resize(new Box($width, $height))
                        ->save($this->getSavePath($attr) . $fileName);
                } else {
                    $image = $this->processImage($file->tempName, $options);
                    $image->save($this->getSavePath($attr) . $fileName);
                }
                $model->{$attr} = $fileName;

                if ($this->issetThumbnails($attr)) {
                    $thumbnails = $this->attributes[$attr]['thumbnails'];
                    foreach ($thumbnails as $name => $options) {
                        $this->ensureAttributes($name, $options);
                        $tmbFileName = $name . '_' . $fileName;
                        $image = $this->processImage($this->getSavePath($attr) . $fileName, $options);
                        $image->save($this->getSavePath($attr) . $tmbFileName);
                    }
                }
            } else {
                $model->{$attr} = $model->oldAttributes[$attr];
            }
        }
    }

    /**
     * @param $object
     * @return string
     */
    private function getShortClassName($object)
    {
        $object = new \ReflectionClass($object);
        return mb_strtolower($object->getShortName());
    }

    /**
     * @param string $original path to original image
     * @param array $options with width and height
     * @return \Imagine\Image\ImageInterface
     */
    private function processImage($original, $options)
    {
        list($imageWidth, $imageHeight) = getimagesize($original);
        $image = Image::getImagine()->open($original);
        if (isset($options['width']) && !isset($options['height'])) {
            $width = $options['width'];
            $height = $options['width'] * $imageHeight / $imageWidth;
            $image->resize(new Box($width, $height));
        } elseif (!isset($options['width']) && isset($options['height'])) {
            $width = $options['height'] * $imageWidth / $imageHeight;
            $height = $options['height'];
            $image->resize(new Box($width, $height));
        } elseif (isset($options['width']) && isset($options['height'])) {
            $width = $options['width'];
            $height = $options['height'];
            if ($width / $height > $imageWidth / $imageHeight) {
                $resizeHeight = $width * $imageHeight / $imageWidth;
                $image->resize(new Box($width, $resizeHeight))
                    ->crop(new Point(0, ($resizeHeight - $height) / 2), new Box($width, $height));
            } else {
                $resizeWidth = $height * $imageWidth / $imageHeight;
                $image->resize(new Box($resizeWidth, $height))
                    ->crop(new Point(($resizeWidth - $width) / 2, 0), new Box($width, $height));
            }
        }

        return $image;
    }

    /**
     * @param string $attr name of attribute
     * @return bool nedd crop or not
     */
    public function needCrop($attr)
    {
        return isset($this->attributes[$attr]['crop']) ? $this->attributes[$attr]['crop'] : $this->crop;
    }

    /**
     * @param string $attr name of attribute
     * @return array|bool false if no coords and array if coords exists
     */
    private function getCoords($attr)
    {
        $post = Yii::$app->request->post($this->owner->formName());
        if ($post === null) {
            return false;
        }
        $x = $post[$attr . '-coords']['x'];
        $y = $post[$attr . '-coords']['y'];
        $w = $post[$attr . '-coords']['w'];
        $h = $post[$attr . '-coords']['h'];
        if (!isset($x, $y, $w, $h)) {
            return false;
        }

        return [
            'x' => $x,
            'y' => $y,
            'w' => $w,
            'h' => $h
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach ($this->attributes as $attr => $options) {
            $this->ensureAttributes($attr, $options);
            $this->deleteFiles($attr);
        }
    }

    /**
     * @param string $attr name of attribute
     * @param bool|string $tmb false or name of thumbnail
     * @return string url to image
     */
    public function getImageUrl($attr, $tmb = false)
    {
        $this->checkAttrExists($attr);
        $prefix = $this->getUrlPrefix($attr);
        if ($tmb) {
            return $prefix . $tmb . '_' . $this->owner->{$attr};
        } else {
            return $prefix . $this->owner->{$attr};
        }
    }

    /**
     * @param string $attr name of attribute
     */
    private function createDirIfNotExists($attr)
    {
        $dir = $this->getSavePath($attr);
        if (!@is_dir($dir)) {
            @mkdir($dir);
        }
    }

    /**
     * @param string $attr name of attribute
     * @param bool|string $tmb name of thumbnail
     * @return bool|string save path
     */
    private function getSavePath($attr, $tmb = false)
    {
        if ($tmb !== false) {
            if (isset($this->attributes[$attr]['thumbnails'][$tmb]['savePathAlias'])) {
                return Yii::getAlias($this->attributes[$attr]['thumbnails'][$tmb]['savePathAlias']);
            }
        }

        if (isset($this->attributes[$attr]['savePathAlias'])) {
            return Yii::getAlias($this->attributes[$attr]['savePathAlias']);
        } elseif (isset($this->savePathAlias)) {
            return Yii::getAlias($this->savePathAlias);
        }

        if (isset(Yii::$aliases['@frontend'])) {
            return Yii::getAlias('@frontend/web/images/' . $this->getShortClassName($this->owner)) . DIRECTORY_SEPARATOR;
        } else {
            return Yii::getAlias('@app/web/images/' . $this->getShortClassName($this->owner)) . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * @param string $attr name of attribute
     * @param bool|string $tmb name of thumbnail
     * @return string url prefix
     */
    private function getUrlPrefix($attr, $tmb = false)
    {
        if ($tmb !== false) {
            if (isset($this->attributes[$attr]['thumbnails'][$tmb]['urlPrefix'])) {
                return $this->attributes[$attr]['thumbnails'][$tmb]['urlPrefix'];
            }
        }

        if (isset($this->attributes[$attr]['urlPrefix'])) {
            return $this->attributes[$attr]['urlPrefix'];
        } elseif (isset($this->urlPrefix)) {
            return $this->urlPrefix;
        } else {
            return '/images/' . $this->getShortClassName($this->owner) . '/';
        }
    }

    /**
     * Delete images
     * @param string $attr name of attribute
     */
    private function deleteFiles($attr)
    {
        $base = $this->getSavePath($attr);
        /* @var $model ActiveRecord */
        $model = $this->owner;
        if ($model->isNewRecord) {
            $value = $model->{$attr};
        } else {
            $value = $model->oldAttributes[$attr];
        }
        $file = $base . $value;

        if (@is_file($file)) {
            @unlink($file);
        }
        if ($this->issetThumbnails($attr)) {
            foreach ($this->attributes[$attr]['thumbnails'] as $name => $options) {
                $this->ensureAttributes($name, $options);
                $file = $base . $name . '_' . $value;
                if (@is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * @param string $attr name of attribute
     * @return bool isset thumbnails or not
     */
    private function issetThumbnails($attr)
    {
        return isset($this->attributes[$attr]['thumbnails']) && is_array($this->attributes[$attr]['thumbnails']);
    }

    /**
     * Check, isset attribute or not
     * @param string $attribute name of attribute
     * @throws InvalidParamException
     */
    private function checkAttrExists($attribute)
    {
        foreach ($this->attributes as $attr => $options) {
            $this->ensureAttributes($attr, $options);
            if ($attr == $attribute) {
                return;
            }
        }
        throw new InvalidParamException();
    }

    /**
     * @param $attr
     * @param $options
     */
    public static function ensureAttributes(&$attr, &$options)
    {
        if (!is_array($options)) {
            $attr = $options;
            $options = [];
        }
    }
}
