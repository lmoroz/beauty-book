<?php
/** @var app\models\Service $model */
use yii\helpers\Html;
use yii\helpers\Url;

$isNew = $model->isNewRecord;
$masters = \app\models\Master::find()->select(['id', 'name'])->orderBy('name')->all();
?>
<form method="post">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
    <div class="card" style="max-width: 500px;">
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
            <input id="f-cat" type="text" name="Service[category]" value="<?= Html::encode($model->category) ?>"
                   placeholder="haircut, nails, skincare...">
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
