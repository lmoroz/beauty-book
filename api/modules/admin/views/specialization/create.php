<?php

/** @var app\models\Specialization $model */

use yii\helpers\Url;

$this->title = 'Новая специализация';
?>

<div class="breadcrumb">
    <a href="<?= Url::to(['index']) ?>">Специализации</a> → Новая
</div>

<h1>Новая специализация</h1>

<?= $this->render('_form', ['model' => $model]) ?>
