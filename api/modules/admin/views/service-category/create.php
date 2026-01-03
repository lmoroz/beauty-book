<?php
/** @var app\models\ServiceCategory $model */
$this->title = 'Новая категория';
?>
<div class="breadcrumb">
    <a href="<?= \yii\helpers\Url::to(['index']) ?>">Категории услуг</a> / Новая
</div>
<h1><?= $this->title ?></h1>
<?= $this->render('_form', ['model' => $model]) ?>
