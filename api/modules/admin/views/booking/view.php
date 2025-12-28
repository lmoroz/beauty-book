<?php
/** @var app\models\Booking $model */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Бронирование #' . $model->id;
?>
<div class="breadcrumb">
    <a href="<?= Url::to(['index']) ?>">Бронирования</a> → #<?= $model->id ?>
</div>

<h1>Бронирование #<?= $model->id ?></h1>

<div class="actions">
    <?php if ($model->status === 'pending'): ?>
        <a href="<?= Url::to(['confirm', 'id' => $model->id]) ?>" class="btn btn-success">✓ Подтвердить</a>
    <?php endif; ?>
    <?php if ($model->status !== 'cancelled'): ?>
        <a href="<?= Url::to(['cancel', 'id' => $model->id]) ?>" class="btn btn-danger"
           onclick="return confirm('Отменить бронирование?')">✕ Отменить</a>
    <?php endif; ?>
</div>

<div class="detail-view">
    <table>
        <tr><th>ID</th><td><?= $model->id ?></td></tr>
        <tr><th>Клиент</th><td><?= Html::encode($model->client_name) ?></td></tr>
        <tr><th>Телефон</th><td><?= Html::encode($model->client_phone) ?></td></tr>
        <tr><th>Email</th><td><?= Html::encode($model->client_email ?? '—') ?></td></tr>
        <tr><th>Мастер</th><td><?= Html::encode($model->timeSlot->master->name ?? '—') ?></td></tr>
        <tr><th>Услуга</th><td><?= Html::encode($model->service->name ?? '—') ?></td></tr>
        <tr><th>Дата</th><td><?= $model->timeSlot->date ?? '—' ?></td></tr>
        <tr><th>Время</th><td><?= substr($model->timeSlot->start_time ?? '', 0, 5) ?>–<?= substr($model->timeSlot->end_time ?? '', 0, 5) ?></td></tr>
        <tr><th>Статус</th><td>
            <?php
            $statusMap = [
                'pending' => ['Ожидает', 'badge-warning'],
                'confirmed' => ['Подтверждена', 'badge-success'],
                'cancelled' => ['Отменена', 'badge-danger'],
                'completed' => ['Выполнена', 'badge-info'],
            ];
            $s = $statusMap[$model->status] ?? [$model->status, 'badge-info'];
            ?>
            <span class="badge <?= $s[1] ?>"><?= $s[0] ?></span>
        </td></tr>
        <tr><th>Примечания</th><td><?= Html::encode($model->notes ?? '—') ?></td></tr>
        <?php if ($model->cancel_reason): ?>
            <tr><th>Причина отмены</th><td><?= Html::encode($model->cancel_reason) ?></td></tr>
        <?php endif; ?>
        <tr><th>Создано</th><td><?= $model->created_at ?></td></tr>
    </table>
</div>
