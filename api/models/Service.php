<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Service model — a service offered by a master.
 *
 * @property int $id
 * @property int $master_id
 * @property string $name
 * @property string|null $description
 * @property int|null $category_id
 * @property int $duration_min
 * @property float $price
 * @property bool $is_active
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Master $master
 * @property ServiceCategory|null $category
 */
class Service extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%services}}';
    }

    public function rules(): array
    {
        return [
            [['master_id', 'name', 'duration_min', 'price'], 'required'],
            [['master_id', 'duration_min', 'sort_order', 'category_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['price'], 'number', 'min' => 0],
            [['duration_min'], 'integer', 'min' => 5, 'max' => 480],
            [['is_active'], 'boolean'],
            [['is_active'], 'default', 'value' => true],
            [['sort_order'], 'default', 'value' => 0],
            [['master_id'], 'exist', 'targetClass' => Master::class, 'targetAttribute' => 'id'],
            [['category_id'], 'exist', 'targetClass' => ServiceCategory::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    public function fields(): array
    {
        return [
            'id',
            'master_id',
            'name',
            'description',
            'category_id',
            'category' => function () {
                $cat = $this->category;
                return $cat ? ['id' => $cat->id, 'name' => $cat->name, 'slug' => $cat->slug] : null;
            },
            'duration_min',
            'price' => function () {
                return (float) $this->price;
            },
            'is_active',
        ];
    }

    public function extraFields(): array
    {
        return ['master'];
    }

    public function getMaster(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Master::class, ['id' => 'master_id']);
    }

    public function getCategory(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ServiceCategory::class, ['id' => 'category_id']);
    }
}
