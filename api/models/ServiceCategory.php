<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Service[] $services
 */
class ServiceCategory extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%service_categories}}';
    }

    public function rules(): array
    {
        return [
            [['name', 'slug'], 'required'],
            [['name'], 'string', 'max' => 191],
            [['slug'], 'string', 'max' => 191],
            [['slug'], 'match', 'pattern' => '/^[a-z0-9\-]+$/'],
            [['name'], 'unique'],
            [['slug'], 'unique'],
            [['sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    public function fields(): array
    {
        return ['id', 'name', 'slug', 'sort_order'];
    }

    public function getServices(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Service::class, ['category_id' => 'id']);
    }
}
