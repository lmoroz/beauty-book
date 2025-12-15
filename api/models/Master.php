<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Master model — an independent specialist who rents a workstation.
 *
 * @property int $id
 * @property int $salon_id
 * @property string $name
 * @property string $slug
 * @property string|null $specialization
 * @property string|null $bio
 * @property string|null $photo
 * @property string|null $phone
 * @property string|null $email
 * @property string $status
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Salon $salon
 * @property Service[] $services
 * @property TimeSlot[] $timeSlots
 */
class Master extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%masters}}';
    }

    public function rules(): array
    {
        return [
            [['salon_id', 'name', 'slug'], 'required'],
            [['salon_id', 'sort_order'], 'integer'],
            [['name', 'email'], 'string', 'max' => 255],
            [['slug'], 'string', 'max' => 255],
            [['slug'], 'match', 'pattern' => '/^[a-z0-9\-]+$/'],
            [['slug'], 'unique', 'targetAttribute' => ['salon_id', 'slug']],
            [['specialization'], 'string', 'max' => 255],
            [['bio'], 'string'],
            [['photo'], 'string', 'max' => 500],
            [['phone'], 'string', 'max' => 20],
            [['email'], 'email'],
            [['status'], 'in', 'range' => ['active', 'inactive', 'on_vacation']],
            [['status'], 'default', 'value' => 'active'],
            [['sort_order'], 'default', 'value' => 0],
            [['salon_id'], 'exist', 'targetClass' => Salon::class, 'targetAttribute' => 'id'],
        ];
    }

    public function fields(): array
    {
        return [
            'id',
            'salon_id',
            'name',
            'slug',
            'specialization',
            'bio',
            'photo',
            'phone',
            'status',
        ];
    }

    public function extraFields(): array
    {
        return ['salon', 'services', 'activeServices'];
    }

    public function getSalon(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Salon::class, ['id' => 'salon_id']);
    }

    public function getServices(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Service::class, ['master_id' => 'id']);
    }

    public function getActiveServices(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Service::class, ['master_id' => 'id'])
            ->andWhere(['is_active' => true])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
    }

    public function getTimeSlots(): \yii\db\ActiveQuery
    {
        return $this->hasMany(TimeSlot::class, ['master_id' => 'id']);
    }
}
