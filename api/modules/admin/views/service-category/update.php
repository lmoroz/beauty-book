<?php
/** @var app\models\ServiceCategory $model */
use yii\helpers\Html;
$this->title = 'Редактировать: ' . Html::encode($model->name);
?>
<div class="breadcrumb">
    <a href="<?= \yii\helpers\Url::to(['index']) ?>">Категории услуг</a> / Редактировать
</div>
<h1><?= $this->title ?></h1>
<?= $this->render('_form', ['model' => $model]) ?>
