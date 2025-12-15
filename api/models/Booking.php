<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Booking model — a client's appointment.
 *
 * @property int $id
 * @property int $time_slot_id
 * @property int $service_id
 * @property string $client_name
 * @property string $client_phone
 * @property string|null $client_email
 * @property string $status
 * @property string|null $notes
 * @property string|null $cancelled_at
 * @property string|null $cancel_reason
 * @property string $created_at
 * @property string $updated_at
 *
 * @property TimeSlot $timeSlot
 * @property Service $service
 */
class Booking extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    public static function tableName(): string
    {
        return '{{%bookings}}';
    }

    public function rules(): array
    {
        return [
            [['time_slot_id', 'service_id', 'client_name', 'client_phone'], 'required'],
            [['time_slot_id', 'service_id'], 'integer'],
            [['client_name'], 'string', 'max' => 255],
            [['client_phone'], 'string', 'max' => 20],
            [['client_email'], 'email'],
            [['status'], 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_CONFIRMED,
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                self::STATUS_NO_SHOW,
            ]],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['notes', 'cancel_reason'], 'string'],
            [['cancel_reason'], 'string', 'max' => 500],
            [['time_slot_id'], 'exist', 'targetClass' => TimeSlot::class, 'targetAttribute' => 'id'],
            [['service_id'], 'exist', 'targetClass' => Service::class, 'targetAttribute' => 'id'],
            [['time_slot_id'], 'unique', 'message' => 'This time slot is already booked.'],
        ];
    }

    public function fields(): array
    {
        return [
            'id',
            'time_slot_id',
            'service_id',
            'client_name',
            'client_phone',
            'client_email',
            'status',
            'notes',
            'created_at',
        ];
    }

    public function extraFields(): array
    {
        return ['timeSlot', 'service'];
    }

    public function getTimeSlot(): \yii\db\ActiveQuery
    {
        return $this->hasOne(TimeSlot::class, ['id' => 'time_slot_id']);
    }

    public function getService(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Service::class, ['id' => 'service_id']);
    }

    /**
     * Cancel the booking.
     */
    public function cancel(?string $reason = null): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = date('Y-m-d H:i:s');
        $this->cancel_reason = $reason;

        if ($this->save(false)) {
            // Release the time slot
            $slot = $this->timeSlot;
            if ($slot) {
                $slot->status = TimeSlot::STATUS_FREE;
                $slot->save(false);
            }
            return true;
        }

        return false;
    }
}
