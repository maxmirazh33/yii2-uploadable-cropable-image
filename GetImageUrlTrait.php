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
        if (mb_strpos(get_class($this), 'backend') === false) {
            $class = new \ReflectionClass($this);
            $class = 'backend\models\\' . $class->getShortName();
            $model = new $class;
            foreach ($model->behaviors as $b) {
                if ($b instanceof Behavior) {
                    return $b->getImageUrl($attr, $tmb, $this);
                }
            }
        }

        foreach ($this->behaviors as $b) {
            if ($b instanceof Behavior) {
                return $b->getImageUrl($attr, $tmb);
            }
        }

        return null;
    }
}
