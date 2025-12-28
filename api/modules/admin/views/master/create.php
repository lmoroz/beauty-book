<?php
/** @var app\models\Master $model */
$this->title = 'Новый мастер';
?>
<div class="breadcrumb">
    <a href="<?= \yii\helpers\Url::to(['index']) ?>">Мастера</a> → Новый
</div>
<h1>Новый мастер</h1>
<?= $this->render('_form', ['model' => $model]) ?>
