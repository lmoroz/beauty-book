<?php
/** @var app\models\Service[] $services */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Услуги';
?>
<h1>Услуги</h1>
<div class="actions">
    <a href="<?= Url::to(['create']) ?>" class="btn btn-success">+ Добавить услугу</a>
</div>
<table class="grid">
    <thead>
    <tr>
        <th>ID</th>
        <th>Мастер</th>
        <th>Название</th>
        <th>Категория</th>
        <th>Время</th>
        <th>Цена ₽</th>
        <th>Активна</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($services as $s): ?>
        <tr>
            <td><?= $s->id ?></td>
            <td><?= Html::encode($s->master->name ?? '—') ?></td>
            <td><strong><?= Html::encode($s->name) ?></strong></td>
            <td><?= Html::encode($s->category->name ?? '—') ?></td>
            <td><?= $s->duration_min ?> мин</td>
            <td><?= number_format($s->price, 0, '.', ' ') ?></td>
            <td>
                <?= $s->is_active
                    ? '<span class="badge badge-success">Да</span>'
                    : '<span class="badge badge-danger">Нет</span>' ?>
            </td>
            <td>
                <a href="<?= Url::to(['update', 'id' => $s->id]) ?>" class="btn btn-primary btn-sm">✏️</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($services)): ?>
        <tr><td colspan="8" style="text-align:center;color:#999;padding:24px;">Услуг пока нет.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
