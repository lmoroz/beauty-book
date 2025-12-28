<?php
$this->title = 'Редактирование: ' . $model->name;
?>
<div class="breadcrumb"><a href="<?= \yii\helpers\Url::to(['index']) ?>">Услуги</a> → Редактирование</div>
<h1>Редактирование: <?= \yii\helpers\Html::encode($model->name) ?></h1>
<?= $this->render('_form', ['model' => $model]) ?>
