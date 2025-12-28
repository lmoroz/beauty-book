<?php
/** @var app\models\Master $model */
$this->title = 'Редактирование: ' . $model->name;
?>
<div class="breadcrumb">
    <a href="<?= \yii\helpers\Url::to(['index']) ?>">Мастера</a> →
    <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>"><?= \yii\helpers\Html::encode($model->name) ?></a> →
    Редактирование
</div>
<h1>Редактирование: <?= \yii\helpers\Html::encode($model->name) ?></h1>
<?= $this->render('_form', ['model' => $model]) ?>
