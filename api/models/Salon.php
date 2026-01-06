<?php

namespace app\models;

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
            [['working_hours', 'settings', 'chat_greeting'], 'safe'],
            [['is_active'], 'boolean'],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        $data = $this->getSettingsArray();
        $this->chat_greeting = isset($data['chat_greeting']) ? $data['chat_greeting'] : '';
    }

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
        $this->settings = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;
        return true;
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
     * @return array
     */
    private function getSettingsArray()
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
