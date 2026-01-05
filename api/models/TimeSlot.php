<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * TimeSlot model — a bookable time slot in a master's schedule.
 *
 * @property int $id
 * @property int $master_id
 * @property string $date
 * @property string $start_time
 * @property string $end_time
 * @property string $status
 * @property string|null $block_reason
 * @property int|null $booking_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Master $master
 * @property Booking $booking
 */
class TimeSlot extends ActiveRecord
{
    public const STATUS_FREE = 'free';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_BLOCKED = 'blocked';

    public const REASON_LUNCH = 'lunch';
    public const REASON_BREAK = 'break';

    public static function tableName(): string
    {
        return '{{%time_slots}}';
    }

    public function rules(): array
    {
        return [
            [['master_id', 'date', 'start_time', 'end_time'], 'required'],
            [['master_id'], 'integer'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['start_time', 'end_time'], 'time', 'format' => 'php:H:i:s'],
            [['status'], 'in', 'range' => [self::STATUS_FREE, self::STATUS_BOOKED, self::STATUS_BLOCKED]],
            [['status'], 'default', 'value' => self::STATUS_FREE],
            [['master_id'], 'exist', 'targetClass' => Master::class, 'targetAttribute' => 'id'],
            [['master_id', 'date', 'start_time'], 'unique', 'targetAttribute' => ['master_id', 'date', 'start_time']],
        ];
    }

    public function fields(): array
    {
        return [
            'id',
            'master_id',
            'date',
            'start_time',
            'end_time',
            'status',
            'block_reason',
            'booking_id',
        ];
    }

    public function extraFields(): array
    {
        return ['master', 'booking'];
    }

    public function getMaster(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Master::class, ['id' => 'master_id']);
    }

    public function getBooking(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Booking::class, ['time_slot_id' => 'id']);
    }

    /**
     * Check if the slot is available for booking.
     */
    public function isFree(): bool
    {
        return $this->status === self::STATUS_FREE;
    }

    /**
     * Find free slots for a master on a given date.
     */
    public static function findFreeSlots(int $masterId, string $date): \yii\db\ActiveQuery
    {
        return static::find()
            ->where([
                'master_id' => $masterId,
                'date' => $date,
                'status' => self::STATUS_FREE,
            ])
            ->orderBy(['start_time' => SORT_ASC]);
    }

    public static function findConsecutiveFreeSlots(int $masterId, string $date, string $startTime, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $slots = static::find()
            ->where([
                'master_id' => $masterId,
                'date' => $date,
                'status' => self::STATUS_FREE,
            ])
            ->andWhere(['>=', 'start_time', $startTime])
            ->orderBy(['start_time' => SORT_ASC])
            ->limit($count)
            ->all();

        if (count($slots) < $count) {
            return [];
        }

        for ($i = 1; $i < $count; $i++) {
            $prevEnd = $slots[$i - 1]->end_time;
            $currStart = $slots[$i]->start_time;
            if ($currStart !== $prevEnd) {
                return [];
            }
        }

        return $slots;
    }
}
