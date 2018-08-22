<?php
use yii\widgets\ActiveForm;
use yii\helpers\Url;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'],'action' => ['post/upload-photos']]) ?>

    <?= $form->field($model, 'imageFile[]')->fileInput(['multiple' => true]) ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>