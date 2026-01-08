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

    /**
     * @param int $masterId
     * @param string $weekStart Y-m-d (Monday)
     * @param array|null $workingHours Salon working_hours structure
     * @return int Number of slots created
     */
    public static function generateWeekSlots($masterId, $weekStart, $workingHours = null)
    {
        if ($workingHours === null) {
            $salon = Salon::find()->where(['is_active' => 1])->limit(1)->one();
            if (!$salon) {
                return 0;
            }
            $wh = $salon->working_hours;
            if (is_string($wh)) {
                $wh = json_decode($wh, true);
            }
            $workingHours = is_array($wh) ? $wh : [];
        }

        $dayKeys = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $created = 0;
        $db = \Yii::$app->db;

        for ($d = 0; $d < 7; $d++) {
            $date = date('Y-m-d', strtotime($weekStart . " +{$d} days"));
            $dayKey = $dayKeys[$d];

            if (!isset($workingHours[$dayKey]) || empty($workingHours[$dayKey])) {
                continue;
            }

            $dayConfig = $workingHours[$dayKey];
            if (!isset($dayConfig['open'], $dayConfig['close'])) {
                continue;
            }

            $openHour = (int) substr($dayConfig['open'], 0, 2);
            $closeHour = (int) substr($dayConfig['close'], 0, 2);

            if ($closeHour <= $openHour) {
                continue;
            }

            $existing = static::find()
                ->where(['master_id' => $masterId, 'date' => $date])
                ->count();

            if ($existing > 0) {
                continue;
            }

            for ($h = $openHour; $h < $closeHour; $h++) {
                $startTime = sprintf('%02d:00:00', $h);
                $endTime = sprintf('%02d:00:00', $h + 1);

                $db->createCommand()->insert(static::tableName(), [
                    'master_id' => $masterId,
                    'date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => self::STATUS_FREE,
                ])->execute();

                $created++;
            }
        }

        return $created;
    }
}
