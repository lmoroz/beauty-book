<?php

/** @var app\models\Master $model */

use yii\helpers\Html;
use yii\helpers\Url;

$isNew = $model->isNewRecord;
?>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <div class="card" style="max-width: 600px;">
        <?php if ($model->hasErrors()): ?>
            <div style="background:#f8d7da;color:#721c24;padding:12px 16px;border-radius:4px;margin-bottom:16px;">
                <strong>Ошибки:</strong>
                <ul style="margin:4px 0 0 16px;">
                    <?php foreach ($model->getFirstErrors() as $error): ?>
                        <li><?= Html::encode($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="f-name">Имя *</label>
            <input id="f-name" type="text" name="Master[name]" value="<?= Html::encode($model->name) ?>" required>
            <?php if ($model->hasErrors('name')): ?>
                <div class="error"><?= $model->getFirstError('name') ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="f-slug">Slug</label>
            <input id="f-slug" type="text" name="Master[slug]" value="<?= Html::encode($model->slug) ?>"
                   placeholder="auto-generated if empty">
        </div>

        <div class="form-group">
            <label for="f-spec">Специализация *</label>
            <select id="f-spec" name="Master[specialization]">
                <?php foreach (['hairdresser', 'manicurist', 'cosmetologist', 'massage', 'stylist', 'other'] as $spec): ?>
                    <option value="<?= $spec ?>" <?= $model->specialization === $spec ? 'selected' : '' ?>>
                        <?= ucfirst($spec) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="f-bio">Био</label>
            <textarea id="f-bio" name="Master[bio]" rows="3"><?= Html::encode($model->bio) ?></textarea>
        </div>

        <div class="form-group">
            <label for="f-phone">Телефон</label>
            <input id="f-phone" type="text" name="Master[phone]" value="<?= Html::encode($model->phone) ?>">
        </div>

        <div class="form-group">
            <label>Фото</label>
            <?php if ($model->photo): ?>
                <div style="margin-bottom: 8px;">
                    <img src="<?= Html::encode($model->photo) ?>" style="max-width: 150px; border-radius: 8px; display: block; margin-bottom: 4px;">
                    <small style="color: #999;"><?= Html::encode($model->photo) ?></small>
                </div>
            <?php endif; ?>
            <input id="f-photo" type="file" name="photo_file" accept="image/*"
                   style="padding: 6px 0;">
            <?php if (!$model->photo): ?>
                <small style="color: #999;">JPG, PNG, WebP — до 5 МБ</small>
            <?php else: ?>
                <small style="color: #999;">Загрузите новое фото для замены</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="f-salon">Салон ID</label>
            <input id="f-salon" type="number" name="Master[salon_id]" value="<?= $model->salon_id ?? 1 ?>">
        </div>

        <div class="form-group">
            <label for="f-status">Статус</label>
            <select id="f-status" name="Master[status]">
                <option value="active" <?= $model->status === 'active' ? 'selected' : '' ?>>Активен</option>
                <option value="inactive" <?= $model->status === 'inactive' ? 'selected' : '' ?>>Неактивен</option>
            </select>
        </div>

        <div class="form-group">
            <label for="f-sort">Порядок сортировки</label>
            <input id="f-sort" type="number" name="Master[sort_order]" value="<?= $model->sort_order ?? 0 ?>">
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary"><?= $isNew ? 'Создать' : 'Сохранить' ?></button>
            <a href="<?= Url::to(['index']) ?>" class="btn" style="background: #eee; color: #333;">Отмена</a>
        </div>
    </div>
</form>
