<?php
/** @var app\models\ServiceCategory $model */
use yii\helpers\Html;
use yii\helpers\Url;

$isNew = $model->isNewRecord;
?>
<form method="post">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
    <div class="card" style="max-width: 500px;">
        <?php if ($model->hasErrors()): ?>
            <div class="flash-error" style="margin-bottom:16px;">
                <?php foreach ($model->getFirstErrors() as $err): ?>
                    <div><?= Html::encode($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="f-name">Название *</label>
            <input id="f-name" type="text" name="ServiceCategory[name]" value="<?= Html::encode($model->name) ?>"
                   placeholder="Стрижки" required>
        </div>
        <div class="form-group">
            <label for="f-slug">Slug</label>
            <input id="f-slug" type="text" name="ServiceCategory[slug]" value="<?= Html::encode($model->slug) ?>"
                   placeholder="haircut (auto-generated if empty)">
        </div>
        <div class="form-group">
            <label for="f-sort">Порядок сортировки</label>
            <input id="f-sort" type="number" name="ServiceCategory[sort_order]"
                   value="<?= $model->sort_order ?? 0 ?>">
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-primary"><?= $isNew ? 'Создать' : 'Сохранить' ?></button>
            <a href="<?= Url::to(['index']) ?>" class="btn" style="background:#eee;color:#333;">Отмена</a>
        </div>
    </div>
</form>
