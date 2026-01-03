<?php
/** @var app\models\Service $model */
use yii\helpers\Html;
use yii\helpers\Url;

$isNew = $model->isNewRecord;
$masters = \app\models\Master::find()->select(['id', 'name'])->orderBy('name')->all();
$categories = \app\models\ServiceCategory::find()->orderBy(['sort_order' => SORT_ASC])->all();
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
            <label for="f-master">Мастер *</label>
            <select id="f-master" name="Service[master_id]" required>
                <option value="">— Выбрать —</option>
                <?php foreach ($masters as $m): ?>
                    <option value="<?= $m->id ?>" <?= $model->master_id == $m->id ? 'selected' : '' ?>>
                        <?= Html::encode($m->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="f-name">Название *</label>
            <input id="f-name" type="text" name="Service[name]" value="<?= Html::encode($model->name) ?>" required>
        </div>
        <div class="form-group">
            <label for="f-cat">Категория</label>
            <select id="f-cat" name="Service[category_id]">
                <option value="">— Без категории —</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat->id ?>" <?= $model->category_id == $cat->id ? 'selected' : '' ?>>
                        <?= Html::encode($cat->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="f-dur">Длительность (мин) *</label>
            <input id="f-dur" type="number" name="Service[duration_min]" value="<?= $model->duration_min ?>" min="5" required>
        </div>
        <div class="form-group">
            <label for="f-price">Цена ₽ *</label>
            <input id="f-price" type="number" name="Service[price]" value="<?= $model->price ?>" min="0" step="100" required>
        </div>
        <div class="form-group">
            <label for="f-sort">Порядок сортировки</label>
            <input id="f-sort" type="number" name="Service[sort_order]" value="<?= $model->sort_order ?? 0 ?>">
        </div>
        <div class="form-group">
            <label for="f-active">Активна</label>
            <select id="f-active" name="Service[is_active]">
                <option value="1" <?= $model->is_active ? 'selected' : '' ?>>Да</option>
                <option value="0" <?= !$model->is_active ? 'selected' : '' ?>>Нет</option>
            </select>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-primary"><?= $isNew ? 'Создать' : 'Сохранить' ?></button>
            <a href="<?= Url::to(['index']) ?>" class="btn" style="background:#eee;color:#333;">Отмена</a>
        </div>
    </div>
</form>
