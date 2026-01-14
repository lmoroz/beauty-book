<?php

/** @var app\models\Master $model */

use app\models\Specialization;
use yii\helpers\Html;
use yii\helpers\Url;

$isNew = $model->isNewRecord;
$specializations = Specialization::find()->orderBy(['sort_order' => SORT_ASC])->all();
$selectedIds = $model->specialization_ids ?: [];
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
            <label>Специализации</label>
            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px;">
                <?php foreach ($specializations as $spec): ?>
                    <label style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: #f3f3f3; border-radius: 6px; cursor: pointer; font-size: 14px; user-select: none;">
                        <input type="checkbox" name="specialization_ids[]" value="<?= $spec->id ?>"
                            <?= in_array($spec->id, $selectedIds) ? 'checked' : '' ?>
                               style="accent-color: #c8a96e;">
                        <?= Html::encode($spec->name) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <?php if (empty($specializations)): ?>
                <small style="color: #999;">Нет доступных специализаций. <a href="<?= Url::to(['/admin/specialization/create']) ?>">Создать</a></small>
            <?php endif; ?>
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
            <input id="f-photo" type="file" name="photo_file" accept=".jpg,.jpeg,.png,.webp"
                   style="padding: 6px 0;">
            <?php if (!$model->photo): ?>
                <small style="color: #999;">JPG, PNG, WebP — до 2 МБ</small>
            <?php else: ?>
                <small style="color: #999;">Загрузите новое фото для замены (JPG, PNG, WebP — до 2 МБ)</small>
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

<?php
$user = $model->isNewRecord ? null : \app\models\User::findOne(['master_id' => $model->id, 'role' => 'master']);
?>

        <div style="border-top: 2px solid #eee; margin-top: 24px; padding-top: 24px;">
            <h3 style="margin: 0 0 16px; font-size: 16px; color: #333;">Учётная запись для входа в кабинет</h3>

            <div class="form-group">
                <label for="f-username">Логин *</label>
                <input id="f-username" type="text" name="User[username]"
                       value="<?= Html::encode($user->username ?? '') ?>"
                       placeholder="anna.petrova" required>
                <small style="color: #999;">Будет использоваться для входа в личный кабинет мастера</small>
            </div>

            <div class="form-group">
                <label for="f-user-email">Email *</label>
                <input id="f-user-email" type="email" name="User[email]"
                       value="<?= Html::encode($user->email ?? '') ?>"
                       placeholder="anna@labellezza.ru" required>
            </div>

            <div class="form-group">
                <label for="f-password"><?= $user ? 'Новый пароль (оставьте пустым для сохранения текущего)' : 'Пароль *' ?></label>
                <input id="f-password" type="password" name="User[password]"
                       placeholder="••••••••" <?= $user ? '' : 'required' ?>>
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary"><?= $isNew ? 'Создать' : 'Сохранить' ?></button>
            <a href="<?= Url::to(['index']) ?>" class="btn" style="background: #eee; color: #333;">Отмена</a>
        </div>
    </div>
</form>
