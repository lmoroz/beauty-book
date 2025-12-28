<?php

/** @var yii\web\View $this */
/** @var int $mastersCount */
/** @var int $servicesCount */
/** @var int $bookingsToday */
/** @var int $bookingsTotal */
/** @var app\models\Booking[] $recentBookings */

use yii\helpers\Html;

$this->title = 'Дашборд';
?>

<h1>Дашборд</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card__value"><?= $mastersCount ?></div>
        <div class="stat-card__label">Активных мастеров</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $servicesCount ?></div>
        <div class="stat-card__label">Услуг в каталоге</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $bookingsToday ?></div>
        <div class="stat-card__label">Записей сегодня</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $bookingsTotal ?></div>
        <div class="stat-card__label">Всего бронирований</div>
    </div>
</div>

<div class="card">
    <h2 style="font-size: 16px; margin-bottom: 16px;">Последние записи</h2>

    <?php if (empty($recentBookings)): ?>
        <p style="color: #999;">Записей пока нет.</p>
    <?php else: ?>
        <table class="grid">
            <thead>
            <tr>
                <th>ID</th>
                <th>Клиент</th>
                <th>Мастер</th>
                <th>Услуга</th>
                <th>Дата</th>
                <th>Статус</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentBookings as $booking): ?>
                <tr>
                    <td><?= $booking->id ?></td>
                    <td><?= Html::encode($booking->client_name) ?></td>
                    <td><?= Html::encode($booking->timeSlot->master->name ?? '—') ?></td>
                    <td><?= Html::encode($booking->service->name ?? '—') ?></td>
                    <td><?= $booking->timeSlot->date ?? '—' ?></td>
                    <td>
                        <?php
                        $statusMap = [
                            'pending' => ['Ожидает', 'badge-warning'],
                            'confirmed' => ['Подтверждена', 'badge-success'],
                            'cancelled' => ['Отменена', 'badge-danger'],
                            'completed' => ['Выполнена', 'badge-info'],
                        ];
                        $s = $statusMap[$booking->status] ?? [$booking->status, 'badge-info'];
                        ?>
                        <span class="badge <?= $s[1] ?>"><?= $s[0] ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
