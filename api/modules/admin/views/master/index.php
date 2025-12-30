<?php

/** @var app\models\Master[] $masters */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Мастера';
?>

<h1>Мастера</h1>

<div class="actions">
    <a href="<?= Url::to(['create']) ?>" class="btn btn-success">+ Добавить мастера</a>
</div>

<table class="grid">
    <thead>
    <tr>
        <th>ID</th>
        <th>Имя</th>
        <th>Специализация</th>
        <th>Телефон</th>
        <th>Статус</th>
        <th>Порядок</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($masters as $master): ?>
        <tr>
            <td><?= $master->id ?></td>
            <td><strong><?= Html::encode($master->name) ?></strong></td>
            <td><?php
                $specs = $master->specializations;
                echo $specs ? implode(', ', array_map(function($s) { return Html::encode($s->name); }, $specs)) : '—';
            ?></td>
            <td><?= Html::encode($master->phone) ?></td>
            <td>
                <?php if ($master->status === 'active'): ?>
                    <span class="badge badge-success">Активен</span>
                <?php else: ?>
                    <span class="badge badge-danger">Неактивен</span>
                <?php endif; ?>
            </td>
            <td><?= $master->sort_order ?></td>
            <td>
                <a href="<?= Url::to(['view', 'id' => $master->id]) ?>" class="btn btn-primary btn-sm">👁</a>
                <a href="<?= Url::to(['update', 'id' => $master->id]) ?>" class="btn btn-primary btn-sm">✏️</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($masters)): ?>
        <tr><td colspan="7" style="text-align: center; color: #999; padding: 24px;">Мастеров пока нет.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
