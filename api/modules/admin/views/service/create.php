<?php
$this->title = 'Новая услуга';
?>
<div class="breadcrumb"><a href="<?= \yii\helpers\Url::to(['index']) ?>">Услуги</a> → Новая</div>
<h1>Новая услуга</h1>
<?= $this->render('_form', ['model' => $model]) ?>
