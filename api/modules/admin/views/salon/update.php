<?php
/** @var app\models\Salon $model */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Настройки салона';
?>
<h1>Настройки салона</h1>

<form method="post">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
    <div class="card" style="max-width: 600px;">
        <div class="form-group">
            <label for="f-name">Название *</label>
            <input id="f-name" type="text" name="Salon[name]" value="<?= Html::encode($model->name) ?>" required>
        </div>
        <div class="form-group">
            <label for="f-slug">Slug</label>
            <input id="f-slug" type="text" name="Salon[slug]" value="<?= Html::encode($model->slug) ?>">
        </div>
        <div class="form-group">
            <label for="f-address">Адрес</label>
            <input id="f-address" type="text" name="Salon[address]" value="<?= Html::encode($model->address) ?>">
        </div>
        <div class="form-group">
            <label for="f-phone">Телефон</label>
            <input id="f-phone" type="text" name="Salon[phone]" value="<?= Html::encode($model->phone) ?>">
        </div>
        <div class="form-group">
            <label for="f-email">Email</label>
            <input id="f-email" type="email" name="Salon[email]" value="<?= Html::encode($model->email) ?>">
        </div>
        <div class="form-group">
            <label for="f-desc">Описание</label>
            <textarea id="f-desc" name="Salon[description]" rows="3"><?= Html::encode($model->description) ?></textarea>
        </div>
        <div class="form-group">
            <label for="f-active">Активен</label>
            <select id="f-active" name="Salon[is_active]">
                <option value="1" <?= $model->is_active ? 'selected' : '' ?>>Да</option>
                <option value="0" <?= !$model->is_active ? 'selected' : '' ?>>Нет</option>
            </select>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </div>
</form>
