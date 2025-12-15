<?php

use yii\db\Migration;

/**
 * Seed test data for development.
 */
class m251211_100000_seed_test_data extends Migration
{
    public function safeUp()
    {
        // Salon
        $this->insert('{{%salons}}', [
            'id' => 1,
            'name' => 'BeautySpace Studio',
            'slug' => 'beautyspace-studio',
            'address' => '123 Main Street, Suite 4',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'info@beautyspace.local',
            'description' => 'Modern beauty salon with coworking model for independent masters.',
            'working_hours' => json_encode([
                'mon' => ['open' => '09:00', 'close' => '21:00'],
                'tue' => ['open' => '09:00', 'close' => '21:00'],
                'wed' => ['open' => '09:00', 'close' => '21:00'],
                'thu' => ['open' => '09:00', 'close' => '21:00'],
                'fri' => ['open' => '09:00', 'close' => '21:00'],
                'sat' => ['open' => '10:00', 'close' => '18:00'],
                'sun' => null,
            ]),
            'is_active' => true,
        ]);

        // Masters
        $masters = [
            [
                'id' => 1,
                'salon_id' => 1,
                'name' => 'Anna Petrova',
                'slug' => 'anna-petrova',
                'specialization' => 'hairdresser',
                'bio' => 'Senior hairdresser with 10 years of experience. Specializes in coloring and complex cuts.',
                'phone' => '+7 (999) 111-11-11',
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'id' => 2,
                'salon_id' => 1,
                'name' => 'Maria Sidorova',
                'slug' => 'maria-sidorova',
                'specialization' => 'manicurist',
                'bio' => 'Nail art expert. Gel, acrylic, and natural nail care.',
                'phone' => '+7 (999) 222-22-22',
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'id' => 3,
                'salon_id' => 1,
                'name' => 'Elena Kozlova',
                'slug' => 'elena-kozlova',
                'specialization' => 'cosmetologist',
                'bio' => 'Licensed cosmetologist. Facials, peels, and anti-aging treatments.',
                'phone' => '+7 (999) 333-33-33',
                'status' => 'active',
                'sort_order' => 3,
            ],
        ];

        foreach ($masters as $master) {
            $this->insert('{{%masters}}', $master);
        }

        // Services
        $services = [
            // Anna — hairdresser
            ['master_id' => 1, 'name' => 'Haircut — Women', 'category' => 'haircut', 'duration_min' => 60, 'price' => 2500, 'sort_order' => 1],
            ['master_id' => 1, 'name' => 'Haircut — Men', 'category' => 'haircut', 'duration_min' => 30, 'price' => 1500, 'sort_order' => 2],
            ['master_id' => 1, 'name' => 'Hair Coloring', 'category' => 'coloring', 'duration_min' => 120, 'price' => 5000, 'sort_order' => 3],
            ['master_id' => 1, 'name' => 'Blowout', 'category' => 'styling', 'duration_min' => 45, 'price' => 1800, 'sort_order' => 4],

            // Maria — manicurist
            ['master_id' => 2, 'name' => 'Classic Manicure', 'category' => 'nails', 'duration_min' => 60, 'price' => 1500, 'sort_order' => 1],
            ['master_id' => 2, 'name' => 'Gel Manicure', 'category' => 'nails', 'duration_min' => 90, 'price' => 2500, 'sort_order' => 2],
            ['master_id' => 2, 'name' => 'Nail Art', 'category' => 'nails', 'duration_min' => 120, 'price' => 3500, 'sort_order' => 3],
            ['master_id' => 2, 'name' => 'Pedicure', 'category' => 'nails', 'duration_min' => 75, 'price' => 2000, 'sort_order' => 4],

            // Elena — cosmetologist
            ['master_id' => 3, 'name' => 'Facial Cleansing', 'category' => 'skincare', 'duration_min' => 60, 'price' => 3000, 'sort_order' => 1],
            ['master_id' => 3, 'name' => 'Chemical Peel', 'category' => 'skincare', 'duration_min' => 45, 'price' => 4000, 'sort_order' => 2],
            ['master_id' => 3, 'name' => 'Anti-Aging Treatment', 'category' => 'skincare', 'duration_min' => 90, 'price' => 6000, 'sort_order' => 3],
        ];

        foreach ($services as $service) {
            $service['is_active'] = true;
            $this->insert('{{%services}}', $service);
        }

        // Time slots for tomorrow (generate a week of slots for each master)
        $startDate = date('Y-m-d'); // today
        for ($d = 0; $d < 7; $d++) {
            $date = date('Y-m-d', strtotime("+{$d} days", strtotime($startDate)));
            $dayOfWeek = date('N', strtotime($date));

            // Skip Sundays
            if ($dayOfWeek == 7) {
                continue;
            }

            $startHour = ($dayOfWeek == 6) ? 10 : 9; // Saturday starts at 10
            $endHour = ($dayOfWeek == 6) ? 18 : 21;   // Saturday ends at 18

            foreach ([1, 2, 3] as $masterId) {
                for ($h = $startHour; $h < $endHour; $h++) {
                    $this->insert('{{%time_slots}}', [
                        'master_id' => $masterId,
                        'date' => $date,
                        'start_time' => sprintf('%02d:00:00', $h),
                        'end_time' => sprintf('%02d:00:00', $h + 1),
                        'status' => 'free',
                    ]);
                }
            }
        }
    }

    public function safeDown()
    {
        $this->delete('{{%bookings}}');
        $this->delete('{{%time_slots}}');
        $this->delete('{{%services}}');
        $this->delete('{{%masters}}');
        $this->delete('{{%salons}}', ['id' => 1]);
    }
}
