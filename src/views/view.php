<?php
/**
 * Image upload view.
 *
 * @var yii\web\View $this
 * @var string $selector Widget ID selector
 * @var yii\db\ActiveRecord $model
 * @var string $attribute
 * @var boolean $crop enable/disable crop
 * @var array $jcropSettings
 */

use yii\bootstrap\Button;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Json;

?>

<?php if ($crop): ?>
    <?php Modal::begin([
        'id' => $selector . '-modal',
        'closeButton' => ['onclick' => 'destroyJcrop("' . $selector . '-image");', 'id' => $selector . '-image-close'],
        'header' => '<h2>' . Yii::t('maxmirazh33/image', 'Crop image') . '</h2>',
        'footer' => Button::widget([
            'label' => 'ОК',
            'options' => [
                'class' => 'btn btn-flat btn-primary',
                'onclick' => '$("#' . $selector . '-image-close").click(); return false;'
            ],
        ]),
    ]); ?>

    <img src="" id="<?= $selector ?>-image">

    <?php Modal::end(); ?>
<?php endif; ?>

<div id="field-<?= $selector ?>" class="form-group uploader">
    <div class="btn btn-default fullinput">
        <div class="uploader-browse">
            <span class="glyphicon glyphicon-picture"></span>
            <span class="browse-text" id="<?= $selector ?>-name">
                <?= Yii::t('maxmirazh33/image', 'Select') ?>
            </span>
            <?= Html::activeFileInput(
                $model,
                $attribute,
                ['id' => $selector, 'onchange' => 'readFile(this, "' . $selector . '", ' . (int)$crop . ', ' . Json::encode($jcropSettings) . ')']
            ) ?>
        </div>
    </div>
    <?php if ($crop): ?>
        <?= Html::hiddenInput($model->formName() . "[$attribute-coords][x]", null, ['id' => "$selector-coords-x"]) ?>
        <?= Html::hiddenInput($model->formName() . "[$attribute-coords][w]", null, ['id' => "$selector-coords-w"]) ?>
        <?= Html::hiddenInput($model->formName() . "[$attribute-coords][y]", null, ['id' => "$selector-coords-y"]) ?>
        <?= Html::hiddenInput($model->formName() . "[$attribute-coords][h]", null, ['id' => "$selector-coords-h"]) ?>
    <?php endif; ?>
</div>
