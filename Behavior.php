<?php
namespace maxmirazh33\image;

use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\imagine\Image;
use yii\validators\ImageValidator;
use yii\validators\Validator;
use yii\web\UploadedFile;
use Yii;

class Behavior extends \yii\base\Behavior
{
    /**
     * @var array list of attribute as $attributeName => [$options]. Options:
     *  $width
     *  $height
     *  $savePathAlias
     *  $extensions
     *  $allowEmpty
     *  $allowEmptyScenarios
     *  $crop
     *  $urlPrefix
     *  $thumbnails - array of thumbnails as $prefix => $options. Options:
     *          $width
     *          $height
     */
    public $attributes = [];
    /**
     * @var string. Default @frontend/web/images/className or @app/web/images/className
     */
    public $savePathAlias;
    /**
     * @var array
     */
    public $extensions = ['jpg', 'jpeg', 'png', 'gif'];
    /**
     * @var bool allow dont't attach image for all scenarios
     */
    public $allowEmpty = false;
    /**
     * @var array scenarios, when allow dont't attach image
     */
    public $allowEmptyScenarios = ['update'];
    /**
     * @var bool enable/disable crop.
     */
    public $crop = true;
    /**
     * @var string part of url for image without hostname. Default '/images/className/'
     */
    public $urlPrefix;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            //ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'beforeValidate',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate($event)
    {
        /**
         * @var ActiveRecord
         */
        $model = $this->owner;
        $validator = new ImageValidator();

        foreach ($this->attributes as $attr => $options) {
            $validator->attributes = [$attr];
            $validator->extensions = isset($options['extensions']) ? $options['extensions'] : $this->extensions;
            $attrAllowEmpty = isset($options['allowEmpty']) ? $options['allowEmpty'] : null;
            $attrAllowEmptyScenarios = isset($options['allowEmptyScenarios']) ? $options['allowEmptyScenarios'] : null;
            if (isset($attrAllowEmpty) && isset($attrAllowEmptyScenarios)) {
                $validator->skipOnEmpty = $attrAllowEmpty || in_array($owner->scenario, $attrAllowEmptyScenarios);
            } elseif (isset($attrAllowEmpty)) {
                $validator->skipOnEmpty = $attrAllowEmpty;
            } elseif (isset($attrAllowEmptyScenarios)) {
                $validator->skipOnEmpty = in_array($owner->sceanrio, $attrAllowEmptyScenarios);
            } else {
                $validator->skipOnEmpty = $this->allowEmpty || in_array($owner->scenario, $this->allowEmptyScenarios);
            }

            $model->validators[] = $validator;
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($event)
    {
        $model = $this->owner;
        foreach ($this->attributes as $attr => $options) {
            if ($file = UploadedFile::getInstance($model, $attr)) {
                $this->createDirIfNotExists($attr);
                if (!$model->isNewRecord) {
                    $this->deleteFiles($attr);
                }
                $fileName = uniqid() . '.' . $file->extension;
                $image = new Image();
                if ($this->needCrop($attr)) {
                    $coords = $this->getCoords($attr);
                    if ($coords == false) {
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
                    $image->crop($file->tempName, $coords['w'], $coords['h'], [$coords['x'], $coords['y']])
                        ->resize(new Box($width, $height))
                        ->save($this->getSavePath($attr) . '/' . $fileName);
                } else {
                    list($imageWidth, $imageHeight) = getimagesize($file->tempName);
                    $image = $image->getImagine()->open($file->tempName);
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
                    $image->save($this->getSavePath($attr) . '/' . $fileName);
                }
                $model->{$attr} = $fileName;

                if ($this->issetThumbnails($attr)) {
                    $thumbnails = $this->attributes[$attr]['thumbnails'];
                    foreach ($thumbnails as $name => $options) {
                        $tmbFileName = $name . '_' . $fileName;
//                        EWideImage::load($file->tempName)
//                            ->resize($this->miniWidth, $this->miniHeight, 'outside')
//                            ->crop(
//                                'center',
//                                'middle',
//                                ($this->miniWidth == null ? '100%' : $this->miniWidth),
//                                ($this->miniHeight == null ? '100%' : $this->miniHeight)
//                            )
//                            ->saveToFile($this->getSavePath() . $fileName);
                    }
                }
            }
        }
    }

    public function needCrop($attr)
    {
        return isset($this->attributes[$attr]['crop']) ? $this->attributes[$attr]['crop'] : $this->crop;
    }

    private function getCoords($attr)
    {
        $post = Yii::$app->request->post($this->owner->formName());
        if ($post === null) {
            return false;
        }
        $x = $post[$attr]['x'];
        $y = $post[$attr]['y'];
        $w = $post[$attr]['w'];
        $h = $post[$attr]['h'];
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

    public function beforeDelete($event)
    {
        foreach ($this->attributes as $attr => $options) {
            $this->deleteFiles($attr);
        }
    }

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

    private function createDirIfNotExists($attr)
    {
        $dir = $this->getSavePath($attr);
        if (!@is_dir($dir)) {
            @mkdir($dir);
        }
    }

    private function getSavePath($attr)
    {
        if (isset($this->attributes[$attr]['savePathAlias'])) {
            return Yii::getAlias($this->attributes[$attr]['savePathAlias']);
        } elseif (isset($this->savePathAlias)) {
            return Yii::getAlias($this->savePathAlias);
        }

        if (isset(Yii::$aliases['@frontend'])) {
            return Yii::getAlias('@frontend/web/images/' . mb_strtolower(basename(str_replace('\\', '/',
                    get_class($this->owner)))));
        } else {
            return Yii::getAlias('@app/web/images/' . mb_strtolower(basename(str_replace('\\', '/',
                    get_class($this->owner)))));
        }
    }

    private function getUrlPrefix($attr)
    {
        if (isset($this->attributes[$attr]['urlPrefix'])) {
            return $this->attributes[$attr]['urlPrefix'];
        } elseif (isset($this->urlPrefix)) {
            return $this->urlPrefix;
        } else {
            return '/images/' . mb_strtolower(basename(str_replace('\\', '/', get_class($this->owner)))) . '/';
        }
    }

    private function deleteFiles($attr)
    {
        $base = $this->getSavePath($attr);
        $file = $base . $attr;
        if (@is_file($file)) {
            @unlink($file);
        }
        if ($this->issetThumbnails($attr)) {
            foreach ($this->attributes[$attr]['thumbnails'] as $name => $options) {
                $file = $base . $name . '_' . $attr;
                if (@is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    private function issetThumbnails($attr)
    {
        return isset($this->attributes[$attr]['thumbnails']) && is_array($this->attributes[$attr]['thumbnails']);
    }

    private function checkAttrExists($attr)
    {
        if (!isset($this->attributes[$attr])) {
            throw new InvalidParamException();
        }
    }
}
