<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $salon_id
 * @property string $name
 * @property string $slug
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
 * @property Specialization[] $specializations
 * @property Service[] $services
 * @property TimeSlot[] $timeSlots
 */
class Master extends ActiveRecord
{
    /** @var int[] */
    public $specialization_ids = [];

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
            [['bio'], 'string'],
            [['photo'], 'string', 'max' => 500],
            [['phone'], 'string', 'max' => 20],
            [['email'], 'email'],
            [['status'], 'in', 'range' => ['active', 'inactive', 'on_vacation']],
            [['status'], 'default', 'value' => 'active'],
            [['sort_order'], 'default', 'value' => 0],
            [['salon_id'], 'exist', 'targetClass' => Salon::class, 'targetAttribute' => 'id'],
            [['specialization_ids'], 'each', 'rule' => ['integer']],
        ];
    }

    public function fields(): array
    {
        return [
            'id',
            'salon_id',
            'name',
            'slug',
            'specializations' => function () {
                return array_map(function (Specialization $s) {
                    return ['id' => $s->id, 'name' => $s->name, 'slug' => $s->slug];
                }, $this->specializations);
            },
            'topServices' => function () {
                $services = $this->getActiveServices()->limit(3)->all();
                return array_map(function (Service $s) {
                    return $s->name;
                }, $services);
            },
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

    public function afterFind(): void
    {
        parent::afterFind();
        $this->specialization_ids = array_map('intval', array_column($this->specializations, 'id'));
    }

    public function getSalon(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Salon::class, ['id' => 'salon_id']);
    }

    public function getSpecializations(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Specialization::class, ['id' => 'specialization_id'])
            ->viaTable('{{%master_specializations}}', ['master_id' => 'id']);
    }

    public function getServices(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Service::class, ['master_id' => 'id']);
    }

    public function getActiveServices(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Service::class, ['master_id' => 'id'])
            ->andWhere(['is_active' => true])
            ->with('category')
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
    }

    public function getTimeSlots(): \yii\db\ActiveQuery
    {
        return $this->hasMany(TimeSlot::class, ['master_id' => 'id']);
    }

    public function saveSpecializations(): void
    {
        $db = static::getDb();
        $table = '{{%master_specializations}}';

        $db->createCommand()->delete($table, ['master_id' => $this->id])->execute();

        $ids = array_filter(array_unique(array_map('intval', $this->specialization_ids)));
        $rows = [];
        foreach ($ids as $specId) {
            $rows[] = [$this->id, $specId];
        }

        if ($rows) {
            $db->createCommand()->batchInsert($table, ['master_id', 'specialization_id'], $rows)->execute();
        }
    }
}
