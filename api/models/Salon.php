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
            [['working_hours', 'settings'], 'safe'],
            [['is_active'], 'boolean'],
        ];
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
