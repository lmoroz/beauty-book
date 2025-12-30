<?php

/** @var app\models\Specialization[] $models */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Специализации';
?>

<h1>Специализации</h1>

<div class="actions">
    <a href="<?= Url::to(['create']) ?>" class="btn btn-success">+ Добавить специализацию</a>
</div>

<table class="grid">
    <thead>
    <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Slug</th>
        <th>Порядок</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($models as $model): ?>
        <tr>
            <td><?= $model->id ?></td>
            <td><strong><?= Html::encode($model->name) ?></strong></td>
            <td><code><?= Html::encode($model->slug) ?></code></td>
            <td><?= $model->sort_order ?></td>
            <td>
                <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="btn btn-primary btn-sm">✏️</a>
                <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Удалить специализацию?')">🗑</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($models)): ?>
        <tr><td colspan="5" style="text-align: center; color: #999; padding: 24px;">Специализаций пока нет.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
