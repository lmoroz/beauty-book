<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Salon model.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $description
 * @property array|null $working_hours
 * @property array|null $settings
 * @property bool $is_active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Master[] $masters
 */
class Salon extends ActiveRecord
{
    const DEFAULT_CHAT_GREETING = 'Здравствуйте! Я помогу подобрать мастера, услугу и удобное время для записи. Расскажите, что вас интересует?';

    /** @var string used by admin form */
    public $chat_greeting;

    /** @var string */
    public $llm_base_url;
    /** @var string */
    public $llm_api_key;
    /** @var string */
    public $llm_model;
    /** @var float|string */
    public $llm_temperature;
    /** @var int|string */
    public $llm_max_tokens;
    /** @var int|string */
    public $llm_timeout;

    /** @var string[] Working hours virtual properties */
    public $wh_mon_open, $wh_mon_close, $wh_mon_closed;
    public $wh_tue_open, $wh_tue_close, $wh_tue_closed;
    public $wh_wed_open, $wh_wed_close, $wh_wed_closed;
    public $wh_thu_open, $wh_thu_close, $wh_thu_closed;
    public $wh_fri_open, $wh_fri_close, $wh_fri_closed;
    public $wh_sat_open, $wh_sat_close, $wh_sat_closed;
    public $wh_sun_open, $wh_sun_close, $wh_sun_closed;

    public static function tableName(): string
    {
        return '{{%salons}}';
    }

    public function rules(): array
    {
        return [
            [['name', 'slug'], 'required'],
            [['name', 'email'], 'string', 'max' => 255],
            [['slug'], 'string', 'max' => 255],
            [['slug'], 'unique'],
            [['slug'], 'match', 'pattern' => '/^[a-z0-9\-]+$/'],
            [['address'], 'string', 'max' => 500],
            [['phone'], 'string', 'max' => 20],
            [['email'], 'email'],
            [['description'], 'string'],
            [['working_hours', 'settings', 'chat_greeting', 'llm_base_url', 'llm_api_key', 'llm_model', 'llm_temperature', 'llm_max_tokens', 'llm_timeout',
                'wh_mon_open', 'wh_mon_close', 'wh_mon_closed',
                'wh_tue_open', 'wh_tue_close', 'wh_tue_closed',
                'wh_wed_open', 'wh_wed_close', 'wh_wed_closed',
                'wh_thu_open', 'wh_thu_close', 'wh_thu_closed',
                'wh_fri_open', 'wh_fri_close', 'wh_fri_closed',
                'wh_sat_open', 'wh_sat_close', 'wh_sat_closed',
                'wh_sun_open', 'wh_sun_close', 'wh_sun_closed',
            ], 'safe'],
            [['is_active'], 'boolean'],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        $data = $this->getSettingsArray();
        $this->chat_greeting = isset($data['chat_greeting']) ? $data['chat_greeting'] : '';
        $this->llm_base_url = isset($data['llm_base_url']) ? $data['llm_base_url'] : '';
        $this->llm_api_key = isset($data['llm_api_key']) ? $data['llm_api_key'] : '';
        $this->llm_model = isset($data['llm_model']) ? $data['llm_model'] : '';
        $this->llm_temperature = isset($data['llm_temperature']) ? $data['llm_temperature'] : '';
        $this->llm_max_tokens = isset($data['llm_max_tokens']) ? $data['llm_max_tokens'] : '';
        $this->llm_timeout = isset($data['llm_timeout']) ? $data['llm_timeout'] : '';

        $this->loadWorkingHours();    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $data = $this->getSettingsArray();

        if ($this->chat_greeting !== null && $this->chat_greeting !== '') {
            $data['chat_greeting'] = $this->chat_greeting;
        } else {
            unset($data['chat_greeting']);
        }

        $llmFields = ['llm_base_url', 'llm_model', 'llm_temperature', 'llm_max_tokens', 'llm_timeout'];
        foreach ($llmFields as $field) {
            if ($this->$field !== null && $this->$field !== '') {
                $data[$field] = $this->$field;
            } else {
                unset($data[$field]);
            }
        }

        if ($this->llm_api_key !== null && $this->llm_api_key !== '' && !$this->isMaskedKey($this->llm_api_key)) {
            $data['llm_api_key'] = $this->llm_api_key;
        }

        $this->settings = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;

        $this->saveWorkingHours();

        return true;
    }

    private static $dayKeys = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    private function loadWorkingHours()
    {
        $wh = $this->working_hours;
        if (is_string($wh)) {
            $wh = json_decode($wh, true);
        }
        if (!is_array($wh)) {
            $wh = [];
        }
        foreach (self::$dayKeys as $day) {
            $openProp = "wh_{$day}_open";
            $closeProp = "wh_{$day}_close";
            $closedProp = "wh_{$day}_closed";
            if (isset($wh[$day]) && is_array($wh[$day])) {
                $this->$openProp = isset($wh[$day]['open']) ? $wh[$day]['open'] : '';
                $this->$closeProp = isset($wh[$day]['close']) ? $wh[$day]['close'] : '';
                $this->$closedProp = 0;
            } else {
                $this->$openProp = '';
                $this->$closeProp = '';
                $this->$closedProp = 1;
            }
        }
    }

    private function saveWorkingHours()
    {
        $wh = [];
        foreach (self::$dayKeys as $day) {
            $closedProp = "wh_{$day}_closed";
            $openProp = "wh_{$day}_open";
            $closeProp = "wh_{$day}_close";
            if ($this->$closedProp) {
                $wh[$day] = null;
            } else {
                $open = $this->$openProp ?: '09:00';
                $close = $this->$closeProp ?: '21:00';
                $wh[$day] = ['open' => $open, 'close' => $close];
            }
        }
        $this->working_hours = json_encode($wh, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function getChatGreeting()
    {
        $data = $this->getSettingsArray();
        return isset($data['chat_greeting']) && $data['chat_greeting'] !== ''
            ? $data['chat_greeting']
            : self::DEFAULT_CHAT_GREETING;
    }

    /**
     * @return string
     */
    public function getMaskedApiKey()
    {
        $data = $this->getSettingsArray();
        $key = isset($data['llm_api_key']) ? $data['llm_api_key'] : '';
        if (empty($key)) {
            return '';
        }
        $len = strlen($key);
        if ($len <= 8) {
            return str_repeat('•', $len);
        }
        return substr($key, 0, 4) . str_repeat('•', $len - 8) . substr($key, -4);
    }

    /**
     * @param string $value
     * @return bool
     */
    private function isMaskedKey($value)
    {
        return (bool) preg_match('/•/', $value);
    }

    /**
     * @return string
     */
    public function getEffectiveLlmBaseUrl()
    {
        $data = $this->getSettingsArray();
        return isset($data['llm_base_url']) && $data['llm_base_url'] !== ''
            ? $data['llm_base_url']
            : Yii::$app->llm->baseUrl;
    }

    /**
     * @return string
     */
    public function getEffectiveLlmApiKey()
    {
        $data = $this->getSettingsArray();
        return isset($data['llm_api_key']) && $data['llm_api_key'] !== ''
            ? $data['llm_api_key']
            : Yii::$app->llm->apiKey;
    }

    /**
     * @return string
     */
    public function getEffectiveLlmModel()
    {
        $data = $this->getSettingsArray();
        return isset($data['llm_model']) && $data['llm_model'] !== ''
            ? $data['llm_model']
            : Yii::$app->llm->model;
    }

    /**
     * @return float
     */
    public function getEffectiveLlmTemperature()
    {
        $data = $this->getSettingsArray();
        return isset($data['llm_temperature']) && $data['llm_temperature'] !== ''
            ? (float) $data['llm_temperature']
            : Yii::$app->llm->temperature;
    }

    /**
     * @return int
     */
    public function getEffectiveLlmMaxTokens()
    {
        $data = $this->getSettingsArray();
        return isset($data['llm_max_tokens']) && $data['llm_max_tokens'] !== ''
            ? (int) $data['llm_max_tokens']
            : Yii::$app->llm->maxTokens;
    }

    /**
     * @return int
     */
    public function getEffectiveLlmTimeout()
    {
        $data = $this->getSettingsArray();
        return isset($data['llm_timeout']) && $data['llm_timeout'] !== ''
            ? (int) $data['llm_timeout']
            : Yii::$app->llm->timeout;
    }

    /**
     * @return array
     */
    public function getSettingsArray()
    {
        if (empty($this->settings)) {
            return [];
        }
        if (is_array($this->settings)) {
            return $this->settings;
        }
        $decoded = json_decode($this->settings, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function fields(): array
    {
        $fields = parent::fields();
        unset($fields['settings']);
        return $fields;
    }

    public function extraFields(): array
    {
        return ['masters', 'activeMasters'];
    }

    public function getMasters(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Master::class, ['salon_id' => 'id']);
    }

    public function getActiveMasters(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Master::class, ['salon_id' => 'id'])
            ->andWhere(['status' => 'active'])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
    }
}
