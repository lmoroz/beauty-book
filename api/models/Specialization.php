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
 * @property Master[] $masters
 */
class Specialization extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%specializations}}';
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

    public function getMasters(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Master::class, ['id' => 'master_id'])
            ->viaTable('{{%master_specializations}}', ['specialization_id' => 'id']);
    }
}
