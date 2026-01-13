<?php

/** @var yii\web\View $this */
/** @var int $mastersCount */
/** @var int $servicesCount */
/** @var int $bookingsToday */
/** @var int $bookingsTotal */
/** @var app\models\Booking[] $recentBookings */
/** @var bool $isSuperAdmin */
/** @var array $snapshotData */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Дашборд';
?>

<h1>Дашборд</h1>

<?php if ($isSuperAdmin): ?>
<div class="card" style="margin-bottom: 24px; border: 2px solid #e74c3c; border-radius: 6px;">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
        <span style="font-size: 18px;">🔒</span>
        <h2 style="font-size: 16px; margin: 0; color: #e74c3c;">Database Protection</h2>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-start;">
        <div style="flex: 1; min-width: 250px;">
            <?php if (!empty($snapshotData['snapshotExists'])): ?>
                <div style="background: #d4edda; padding: 10px 14px; border-radius: 4px; font-size: 13px; margin-bottom: 12px;">
                    <strong>Последний снэпшот:</strong> <?= Html::encode($snapshotData['snapshotDate']) ?>
                    <span style="color: #666;">(<?= $snapshotData['snapshotSize'] ?> KB)</span>
                </div>
            <?php else: ?>
                <div style="background: #fff3cd; padding: 10px 14px; border-radius: 4px; font-size: 13px; margin-bottom: 12px;">
                    Снэпшот ещё не создан
                </div>
            <?php endif; ?>

            <form method="post" action="<?= Url::to(['/admin/default/snapshot']) ?>" style="display: inline;">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Создать снэпшот текущего состояния БД и файлов?')">
                    📸 Сделать снэпшот
                </button>
            </form>
        </div>

        <div style="flex: 0 0 auto; padding: 12px 16px; background: #f8f9fa; border-radius: 6px;">
            <form method="post" action="<?= Url::to(['/admin/default/toggle-reset']) ?>">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px;">
                    <input type="hidden" name="toggle" value="1">
                    <input
                        type="checkbox"
                        onchange="this.form.submit()"
                        <?= !empty($snapshotData['autoResetEnabled']) ? 'checked' : '' ?>
                        style="width: 18px; height: 18px; cursor: pointer;"
                    >
                    <span>
                        <strong>Автосброс в полночь</strong><br>
                        <span style="color: #999; font-size: 12px;">Ежедневно в 00:00 MSK → сброс до снэпшота</span>
                    </span>
                </label>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

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
