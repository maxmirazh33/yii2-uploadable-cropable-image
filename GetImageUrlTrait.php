<?php
namespace maxmirazh33\image;

trait GetImageUrlTrait
{
    /**
     * @param string $attr name of attribute
     * @param bool|string $tmb false or name of thumbnail
     * @return null|string url to image
     */
    public function getImageUrl($attr, $tmb = false)
    {
        foreach ($this->behaviors as $behavior) {
            if ($behavior instanceof Behavior) {
                return $behavior->getImageUrl($attr, $tmb);
            }
        }

        $class = new \ReflectionClass($this);
        $class = 'backend\models\\' . $class->getShortName();
        if (class_exists($class)) {
            $model = new $class;
            foreach ($model->behaviors as $behavior) {
                if ($behavior instanceof Behavior) {
                    return $behavior->getImageUrl($attr, $tmb, $this);
                }
            }
        }

        return null;
    }
}
