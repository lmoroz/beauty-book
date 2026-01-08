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

        <h3 style="margin-top: 24px;">Рабочие часы</h3>
        <p style="color: #888; font-size: 13px; margin-bottom: 12px;">
            Расписание работы салона. При просмотре расписания мастером слоты генерируются автоматически.
        </p>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
            <thead>
                <tr style="text-align: left; border-bottom: 1px solid #ddd;">
                    <th style="padding: 6px 8px; width: 80px;">День</th>
                    <th style="padding: 6px 8px;">Открытие</th>
                    <th style="padding: 6px 8px;">Закрытие</th>
                    <th style="padding: 6px 8px; width: 80px;">Выходной</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dayLabels = [
                    'mon' => 'Пн', 'tue' => 'Вт', 'wed' => 'Ср',
                    'thu' => 'Чт', 'fri' => 'Пт', 'sat' => 'Сб', 'sun' => 'Вс',
                ];
                foreach ($dayLabels as $key => $label):
                    $openProp = "wh_{$key}_open";
                    $closeProp = "wh_{$key}_close";
                    $closedProp = "wh_{$key}_closed";
                    $isClosed = (bool) $model->$closedProp;
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 6px 8px; font-weight: 600;"><?= $label ?></td>
                    <td style="padding: 6px 8px;">
                        <input type="time" name="Salon[<?= $openProp ?>]"
                               value="<?= Html::encode($model->$openProp) ?>"
                               id="f-<?= $openProp ?>"
                               style="width: 120px;"
                               <?= $isClosed ? 'disabled' : '' ?>>
                    </td>
                    <td style="padding: 6px 8px;">
                        <input type="time" name="Salon[<?= $closeProp ?>]"
                               value="<?= Html::encode($model->$closeProp) ?>"
                               id="f-<?= $closeProp ?>"
                               style="width: 120px;"
                               <?= $isClosed ? 'disabled' : '' ?>>
                    </td>
                    <td style="padding: 6px 8px; text-align: center;">
                        <input type="hidden" name="Salon[<?= $closedProp ?>]" value="0" id="h-<?= $closedProp ?>">
                        <input type="checkbox" name="Salon[<?= $closedProp ?>]" value="1"
                               id="f-<?= $closedProp ?>"
                               <?= $isClosed ? 'checked' : '' ?>
                               onchange="toggleDay('<?= $key ?>')">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 style="margin-top: 24px;">Чат-бот</h3>

        <div class="form-group">
            <label for="f-greeting">Приветственное сообщение бота</label>
            <textarea id="f-greeting" name="Salon[chat_greeting]" rows="3" placeholder="<?= Html::encode(\app\models\Salon::DEFAULT_CHAT_GREETING) ?>"><?= Html::encode($model->chat_greeting) ?></textarea>
            <small style="color: #888;">Первое сообщение, которое видит клиент при открытии чата. Оставьте пустым для текста по умолчанию.</small>
        </div>

        <h3 style="margin-top: 24px;">LLM API</h3>
        <p style="color: #888; font-size: 13px; margin-bottom: 16px;">
            Настройки подключения к AI-провайдеру. Пустые поля берут значения из конфигурации сервера.
        </p>

        <div class="form-group">
            <label for="f-llm-base-url">Base URL</label>
            <input id="f-llm-base-url" type="text" name="Salon[llm_base_url]"
                   value="<?= Html::encode($model->llm_base_url) ?>"
                   placeholder="<?= Html::encode(Yii::$app->llm->baseUrl) ?>">
        </div>
        <div class="form-group">
            <label for="f-llm-api-key">API Key</label>
            <div style="position: relative;">
                <input id="f-llm-api-key" type="password" name="Salon[llm_api_key]"
                       value="<?= Html::encode($model->getMaskedApiKey()) ?>"
                       placeholder="Не задан — используется серверный"
                       autocomplete="off">
                <button type="button" id="toggle-api-key"
                        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; color: #888; padding: 4px;"
                        title="Показать/скрыть">👁</button>
            </div>
            <small style="color: #888;">Для изменения ключа введите новое значение целиком. Текущий ключ замаскирован.</small>
        </div>
        <div class="form-group">
            <label for="f-llm-model">Модель</label>
            <input id="f-llm-model" type="text" name="Salon[llm_model]"
                   value="<?= Html::encode($model->llm_model) ?>"
                   placeholder="<?= Html::encode(Yii::$app->llm->model) ?>">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
            <div class="form-group">
                <label for="f-llm-temp">Temperature</label>
                <input id="f-llm-temp" type="number" step="0.1" min="0" max="2" name="Salon[llm_temperature]"
                       value="<?= Html::encode($model->llm_temperature) ?>"
                       placeholder="<?= Yii::$app->llm->temperature ?>">
            </div>
            <div class="form-group">
                <label for="f-llm-tokens">Max Tokens</label>
                <input id="f-llm-tokens" type="number" step="1" min="1" max="128000" name="Salon[llm_max_tokens]"
                       value="<?= Html::encode($model->llm_max_tokens) ?>"
                       placeholder="<?= Yii::$app->llm->maxTokens ?>">
            </div>
            <div class="form-group">
                <label for="f-llm-timeout">Timeout (сек)</label>
                <input id="f-llm-timeout" type="number" step="1" min="5" max="120" name="Salon[llm_timeout]"
                       value="<?= Html::encode($model->llm_timeout) ?>"
                       placeholder="<?= Yii::$app->llm->timeout ?>">
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </div>
</form>

<script>
function toggleDay(day) {
    var cb = document.getElementById('f-wh_' + day + '_closed');
    var openInput = document.getElementById('f-wh_' + day + '_open');
    var closeInput = document.getElementById('f-wh_' + day + '_close');
    openInput.disabled = cb.checked;
    closeInput.disabled = cb.checked;
}

document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('f-llm-api-key');
    var btn = document.getElementById('toggle-api-key');
    btn.addEventListener('click', function () {
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🔒';
        } else {
            input.type = 'password';
            btn.textContent = '👁';
        }
    });
    input.addEventListener('focus', function () {
        if (input.value && /•/.test(input.value)) {
            input.value = '';
            input.type = 'text';
            btn.textContent = '🔒';
        }
    });

    // Re-enable disabled inputs on submit so values get posted
    document.querySelector('form').addEventListener('submit', function () {
        var inputs = this.querySelectorAll('input[disabled]');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].disabled = false;
        }
    });
});
</script>
