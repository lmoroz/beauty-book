<?php

/** @var app\models\Master $model */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $model->name;
?>

<div class="breadcrumb">
    <a href="<?= Url::to(['index']) ?>">Мастера</a> → <?= Html::encode($model->name) ?>
</div>

<h1><?= Html::encode($model->name) ?></h1>

<div class="actions">
    <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="btn btn-primary">Редактировать</a>
    <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>" class="btn btn-danger"
       onclick="return confirm('Деактивировать мастера?')">Деактивировать</a>
</div>

<div class="detail-view">
    <table>
        <tr><th>ID</th><td><?= $model->id ?></td></tr>
        <tr><th>Имя</th><td><?= Html::encode($model->name) ?></td></tr>
        <tr><th>Slug</th><td><?= Html::encode($model->slug) ?></td></tr>
        <tr><th>Специализация</th><td><?= Html::encode($model->specialization) ?></td></tr>
        <tr><th>Био</th><td><?= Html::encode($model->bio) ?></td></tr>
        <tr><th>Телефон</th><td><?= Html::encode($model->phone) ?></td></tr>
        <tr><th>Фото</th><td><?= $model->photo ? Html::img($model->photo, ['style' => 'max-width:200px']) : '—' ?></td></tr>
        <tr><th>Статус</th><td>
            <?php if ($model->status === 'active'): ?>
                <span class="badge badge-success">Активен</span>
            <?php else: ?>
                <span class="badge badge-danger">Неактивен</span>
            <?php endif; ?>
        </td></tr>
        <tr><th>Порядок сортировки</th><td><?= $model->sort_order ?></td></tr>
        <tr><th>Создан</th><td><?= $model->created_at ?></td></tr>
        <tr><th>Обновлён</th><td><?= $model->updated_at ?></td></tr>
    </table>
</div>
