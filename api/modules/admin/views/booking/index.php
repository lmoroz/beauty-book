<?php
/** @var app\models\Booking[] $bookings */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Бронирования';

$statusMap = [
    'pending' => ['Ожидает', 'badge-warning'],
    'confirmed' => ['Подтверждена', 'badge-success'],
    'cancelled' => ['Отменена', 'badge-danger'],
    'completed' => ['Выполнена', 'badge-info'],
];
?>
<h1>Бронирования</h1>

<div class="actions">
    <a href="<?= Url::to(['index']) ?>" class="btn <?= !Yii::$app->request->get('status') ? 'btn-primary' : '' ?> btn-sm">Все</a>
    <a href="<?= Url::to(['index', 'status' => 'pending']) ?>" class="btn <?= Yii::$app->request->get('status') === 'pending' ? 'btn-primary' : '' ?> btn-sm">Ожидающие</a>
    <a href="<?= Url::to(['index', 'status' => 'confirmed']) ?>" class="btn <?= Yii::$app->request->get('status') === 'confirmed' ? 'btn-primary' : '' ?> btn-sm">Подтверждённые</a>
    <a href="<?= Url::to(['index', 'status' => 'cancelled']) ?>" class="btn <?= Yii::$app->request->get('status') === 'cancelled' ? 'btn-primary' : '' ?> btn-sm">Отменённые</a>
</div>

<table class="grid">
    <thead>
    <tr>
        <th>ID</th>
        <th>Клиент</th>
        <th>Телефон</th>
        <th>Мастер</th>
        <th>Услуга</th>
        <th>Дата</th>
        <th>Время</th>
        <th>Статус</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($bookings as $b): ?>
        <tr>
            <td><?= $b->id ?></td>
            <td><?= Html::encode($b->client_name) ?></td>
            <td><?= Html::encode($b->client_phone) ?></td>
            <td><?= Html::encode($b->timeSlot->master->name ?? '—') ?></td>
            <td><?= Html::encode($b->service->name ?? '—') ?></td>
            <td><?= $b->timeSlot->date ?? '—' ?></td>
            <td><?= substr($b->timeSlot->start_time ?? '', 0, 5) ?>–<?= substr($b->timeSlot->end_time ?? '', 0, 5) ?></td>
            <td>
                <?php $s = $statusMap[$b->status] ?? [$b->status, 'badge-info']; ?>
                <span class="badge <?= $s[1] ?>"><?= $s[0] ?></span>
            </td>
            <td>
                <a href="<?= Url::to(['view', 'id' => $b->id]) ?>" class="btn btn-primary btn-sm">👁</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($bookings)): ?>
        <tr><td colspan="9" style="text-align:center;color:#999;padding:24px;">Записей нет.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
