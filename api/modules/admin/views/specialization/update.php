<?php

/** @var app\models\Specialization $model */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Редактировать: ' . $model->name;
?>

<div class="breadcrumb">
    <a href="<?= Url::to(['index']) ?>">Специализации</a> → <?= Html::encode($model->name) ?>
</div>

<h1>Редактировать: <?= Html::encode($model->name) ?></h1>

<?= $this->render('_form', ['model' => $model]) ?>
