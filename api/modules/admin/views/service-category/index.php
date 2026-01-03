<?php
/** @var app\models\ServiceCategory[] $models */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Категории услуг';
?>
<h1>Категории услуг</h1>
<div class="actions">
    <a href="<?= Url::to(['create']) ?>" class="btn btn-success">+ Добавить категорию</a>
</div>
<table class="grid">
    <thead>
    <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Slug</th>
        <th>Порядок</th>
        <th>Услуг</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($models as $m): ?>
        <tr>
            <td><?= $m->id ?></td>
            <td><strong><?= Html::encode($m->name) ?></strong></td>
            <td><code><?= Html::encode($m->slug) ?></code></td>
            <td><?= $m->sort_order ?></td>
            <td><?= $m->getServices()->count() ?></td>
            <td>
                <a href="<?= Url::to(['update', 'id' => $m->id]) ?>" class="btn btn-primary btn-sm">✏️</a>
                <a href="<?= Url::to(['delete', 'id' => $m->id]) ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Удалить категорию?')">🗑</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($models)): ?>
        <tr><td colspan="6" style="text-align:center;color:#999;padding:24px;">Категорий пока нет.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
