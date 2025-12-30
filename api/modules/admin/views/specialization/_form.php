<?php

/** @var app\models\Specialization $model */

use yii\helpers\Html;
use yii\helpers\Url;

$isNew = $model->isNewRecord;
?>

<form method="post">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <div class="card" style="max-width: 500px;">
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
            <label for="f-name">Название *</label>
            <input id="f-name" type="text" name="Specialization[name]" value="<?= Html::encode($model->name) ?>"
                   required placeholder="Например: Hairdresser">
        </div>

        <div class="form-group">
            <label for="f-slug">Slug</label>
            <input id="f-slug" type="text" name="Specialization[slug]" value="<?= Html::encode($model->slug) ?>"
                   placeholder="auto-generated if empty">
        </div>

        <div class="form-group">
            <label for="f-sort">Порядок сортировки</label>
            <input id="f-sort" type="number" name="Specialization[sort_order]" value="<?= $model->sort_order ?? 0 ?>">
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary"><?= $isNew ? 'Создать' : 'Сохранить' ?></button>
            <a href="<?= Url::to(['index']) ?>" class="btn" style="background: #eee; color: #333;">Отмена</a>
        </div>
    </div>
</form>
