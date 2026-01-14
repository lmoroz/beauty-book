<?php

use yii\db\Migration;

class m260114_100000_reseed_services_from_dikidi extends Migration
{
    public function safeUp()
    {
        $this->delete('{{%bookings}}');
        $this->delete('{{%services}}');

        $this->insert('{{%service_categories}}', [
            'name' => 'Ресницы и брови',
            'slug' => 'lashes-brows',
            'sort_order' => 6,
        ]);
        $lashBrowId = $this->db->getLastInsertID();

        $this->insert('{{%service_categories}}', [
            'name' => 'Депиляция',
            'slug' => 'waxing',
            'sort_order' => 7,
        ]);
        $waxingId = $this->db->getLastInsertID();

        $catHaircut = 1;
        $catColoring = 2;
        $catStyling = 3;
        $catNails = 4;

        // ====================================================================
        // Master 1: Anna Petrova — hairdresser
        // Categories: Стрижки, Окрашивание, Укладки
        // Source: Парикмахерский зал (жен.), (муж.), Окрашивание и восстановление
        // ====================================================================
        $annaServices = [
            // -- Стрижки --
            ['name' => 'Стрижка короткие волосы', 'category_id' => $catHaircut, 'duration_min' => 45, 'price' => 1200, 'sort_order' => 1],
            ['name' => 'Стрижка средние волосы (до плеч)', 'category_id' => $catHaircut, 'duration_min' => 60, 'price' => 1500, 'sort_order' => 2],
            ['name' => 'Стрижка длинные волосы (ниже плеч)', 'category_id' => $catHaircut, 'duration_min' => 60, 'price' => 1800, 'sort_order' => 3],
            ['name' => 'Стрижка очень длинные (до лопаток)', 'category_id' => $catHaircut, 'duration_min' => 60, 'price' => 2200, 'sort_order' => 4],
            ['name' => 'Стрижка короткая креативная', 'category_id' => $catHaircut, 'duration_min' => 60, 'price' => 1500, 'sort_order' => 5],
            ['name' => 'Стрижка мужская', 'category_id' => $catHaircut, 'duration_min' => 30, 'price' => 900, 'sort_order' => 6],
            ['name' => 'Креативная мужская стрижка', 'category_id' => $catHaircut, 'duration_min' => 30, 'price' => 1000, 'sort_order' => 7],
            ['name' => 'Детская стрижка (до 8 лет)', 'category_id' => $catHaircut, 'duration_min' => 30, 'price' => 800, 'sort_order' => 8],
            ['name' => 'Чёлка с оформлением у лица', 'category_id' => $catHaircut, 'duration_min' => 20, 'price' => 450, 'sort_order' => 9],
            ['name' => 'Оформление чёлки (с нуля)', 'category_id' => $catHaircut, 'duration_min' => 20, 'price' => 700, 'sort_order' => 10],

            // -- Окрашивание --
            ['name' => 'Окрашивание корней (до 2 см)', 'category_id' => $catColoring, 'duration_min' => 90, 'price' => 3300, 'sort_order' => 11],
            ['name' => 'Окрашивание 1 тон (до плеч)', 'category_id' => $catColoring, 'duration_min' => 100, 'price' => 3800, 'sort_order' => 12],
            ['name' => 'Окрашивание 1 тон (ниже плеч)', 'category_id' => $catColoring, 'duration_min' => 120, 'price' => 4200, 'sort_order' => 13],
            ['name' => 'Окрашивание 1 тон (до лопаток)', 'category_id' => $catColoring, 'duration_min' => 120, 'price' => 5500, 'sort_order' => 14],
            ['name' => 'Мелирование + тонирование', 'category_id' => $catColoring, 'duration_min' => 210, 'price' => 7900, 'sort_order' => 15],
            ['name' => 'Сложное окрашивание (до плеч)', 'category_id' => $catColoring, 'duration_min' => 210, 'price' => 12000, 'sort_order' => 16],
            ['name' => 'Сложное окрашивание (ниже плеч)', 'category_id' => $catColoring, 'duration_min' => 270, 'price' => 18000, 'sort_order' => 17],
            ['name' => 'Контуринг', 'category_id' => $catColoring, 'duration_min' => 180, 'price' => 4500, 'sort_order' => 18],
            ['name' => 'Камуфляж седины (для мужчин)', 'category_id' => $catColoring, 'duration_min' => 60, 'price' => 1800, 'sort_order' => 19],

            // -- Укладки --
            ['name' => 'Укладка по форме (до плеч)', 'category_id' => $catStyling, 'duration_min' => 60, 'price' => 1100, 'sort_order' => 20],
            ['name' => 'Укладка по форме (ниже плеч)', 'category_id' => $catStyling, 'duration_min' => 60, 'price' => 1400, 'sort_order' => 21],
            ['name' => 'Укладка локоны (до плеч)', 'category_id' => $catStyling, 'duration_min' => 60, 'price' => 2000, 'sort_order' => 22],
            ['name' => 'Укладка локоны (до лопаток)', 'category_id' => $catStyling, 'duration_min' => 90, 'price' => 3500, 'sort_order' => 23],
            ['name' => 'Причёска', 'category_id' => $catStyling, 'duration_min' => 120, 'price' => 3500, 'sort_order' => 24],
            ['name' => 'Карвинг (долговременная завивка)', 'category_id' => $catStyling, 'duration_min' => 150, 'price' => 4500, 'sort_order' => 25],
            ['name' => 'Ампульный уход для восстановления волос', 'category_id' => $catStyling, 'duration_min' => 60, 'price' => 2000, 'sort_order' => 26],
        ];

        // ====================================================================
        // Master 2: Maria Sidorova — manicurist
        // Category: Ногтевой сервис
        // ====================================================================
        $mariaServices = [
            ['name' => 'Маникюр комбинированный', 'category_id' => $catNails, 'duration_min' => 45, 'price' => 1200, 'sort_order' => 1],
            ['name' => 'Маникюр + выравнивание + покрытие (со снятием)', 'category_id' => $catNails, 'duration_min' => 120, 'price' => 2400, 'sort_order' => 2],
            ['name' => 'Маникюр + выравнивание + покрытие (без снятия)', 'category_id' => $catNails, 'duration_min' => 90, 'price' => 2200, 'sort_order' => 3],
            ['name' => 'Маникюр + гель (укрепление) со снятием', 'category_id' => $catNails, 'duration_min' => 120, 'price' => 2800, 'sort_order' => 4],
            ['name' => 'Маникюр + лечебное покрытие (без снятия)', 'category_id' => $catNails, 'duration_min' => 60, 'price' => 1700, 'sort_order' => 5],
            ['name' => 'Мужской маникюр', 'category_id' => $catNails, 'duration_min' => 45, 'price' => 1400, 'sort_order' => 6],
            ['name' => 'Наращивание ногтей', 'category_id' => $catNails, 'duration_min' => 180, 'price' => 4000, 'sort_order' => 7],
            ['name' => 'Коррекция наращенных ногтей', 'category_id' => $catNails, 'duration_min' => 120, 'price' => 3000, 'sort_order' => 8],
            ['name' => 'Педикюр полный Smart (без покрытия)', 'category_id' => $catNails, 'duration_min' => 60, 'price' => 2500, 'sort_order' => 9],
            ['name' => 'Педикюр полный Smart с покрытием (со снятием)', 'category_id' => $catNails, 'duration_min' => 120, 'price' => 3300, 'sort_order' => 10],
            ['name' => 'Педикюр пальчики', 'category_id' => $catNails, 'duration_min' => 45, 'price' => 1600, 'sort_order' => 11],
            ['name' => 'Педикюр пальчики + покрытие (без снятия)', 'category_id' => $catNails, 'duration_min' => 90, 'price' => 2400, 'sort_order' => 12],
            ['name' => 'Педикюр стопы', 'category_id' => $catNails, 'duration_min' => 30, 'price' => 1200, 'sort_order' => 13],
            ['name' => 'Мужской педикюр Smart', 'category_id' => $catNails, 'duration_min' => 90, 'price' => 3000, 'sort_order' => 14],
            ['name' => 'Комплекс маникюр + педикюр Smart (со снятием)', 'category_id' => $catNails, 'duration_min' => 210, 'price' => 5700, 'sort_order' => 15],
            ['name' => 'Снятие гель-лака без покрытия', 'category_id' => $catNails, 'duration_min' => 20, 'price' => 500, 'sort_order' => 16],
            ['name' => 'Ремонт ногтя', 'category_id' => $catNails, 'duration_min' => 15, 'price' => 250, 'sort_order' => 17],
            ['name' => 'Френч (дизайн)', 'category_id' => $catNails, 'duration_min' => 30, 'price' => 500, 'sort_order' => 18],
        ];

        // ====================================================================
        // Master 3: Elena Kozlova — cosmetologist
        // Categories: Ресницы и брови, Депиляция
        // ====================================================================
        $elenaServices = [
            // -- Ресницы и брови --
            ['name' => 'Классическое наращивание ресниц', 'category_id' => $lashBrowId, 'duration_min' => 105, 'price' => 2500, 'sort_order' => 1],
            ['name' => '2D наращивание ресниц', 'category_id' => $lashBrowId, 'duration_min' => 135, 'price' => 3000, 'sort_order' => 2],
            ['name' => '3D наращивание ресниц', 'category_id' => $lashBrowId, 'duration_min' => 150, 'price' => 3400, 'sort_order' => 3],
            ['name' => 'Ламинирование ресниц', 'category_id' => $lashBrowId, 'duration_min' => 60, 'price' => 2500, 'sort_order' => 4],
            ['name' => 'Ламинирование ресниц + ботокс', 'category_id' => $lashBrowId, 'duration_min' => 75, 'price' => 2700, 'sort_order' => 5],
            ['name' => 'Снятие ресниц (без наращивания)', 'category_id' => $lashBrowId, 'duration_min' => 30, 'price' => 500, 'sort_order' => 6],
            ['name' => 'Архитектура бровей (краска)', 'category_id' => $lashBrowId, 'duration_min' => 40, 'price' => 1500, 'sort_order' => 7],
            ['name' => 'Коррекция бровей (воск + пинцет)', 'category_id' => $lashBrowId, 'duration_min' => 30, 'price' => 800, 'sort_order' => 8],
            ['name' => 'Ламинирование бровей (с окрашиванием и коррекцией)', 'category_id' => $lashBrowId, 'duration_min' => 60, 'price' => 2300, 'sort_order' => 9],
            ['name' => 'Окрашивание бровей (краска)', 'category_id' => $lashBrowId, 'duration_min' => 30, 'price' => 800, 'sort_order' => 10],
            ['name' => 'Окрашивание ресниц', 'category_id' => $lashBrowId, 'duration_min' => 30, 'price' => 500, 'sort_order' => 11],

            // -- Депиляция --
            ['name' => 'Бикини глубокое (воск)', 'category_id' => $waxingId, 'duration_min' => 30, 'price' => 2100, 'sort_order' => 12],
            ['name' => 'Бикини классика (воск)', 'category_id' => $waxingId, 'duration_min' => 20, 'price' => 1200, 'sort_order' => 13],
            ['name' => 'Ноги полностью (воск)', 'category_id' => $waxingId, 'duration_min' => 40, 'price' => 2300, 'sort_order' => 14],
            ['name' => 'Ноги 1/2 (воск)', 'category_id' => $waxingId, 'duration_min' => 30, 'price' => 1200, 'sort_order' => 15],
            ['name' => 'Подмышечные впадины (воск)', 'category_id' => $waxingId, 'duration_min' => 15, 'price' => 700, 'sort_order' => 16],
            ['name' => 'Руки полностью (воск)', 'category_id' => $waxingId, 'duration_min' => 20, 'price' => 1300, 'sort_order' => 17],
            ['name' => 'Верхняя губа (воск)', 'category_id' => $waxingId, 'duration_min' => 10, 'price' => 400, 'sort_order' => 18],
            ['name' => 'Комплекс: ноги + бикини + подмышки (воск)', 'category_id' => $waxingId, 'duration_min' => 90, 'price' => 4500, 'sort_order' => 19],
        ];

        $allServices = [
            1 => $annaServices,
            2 => $mariaServices,
            3 => $elenaServices,
        ];

        foreach ($allServices as $masterId => $services) {
            foreach ($services as $service) {
                $this->insert('{{%services}}', [
                    'master_id' => $masterId,
                    'name' => $service['name'],
                    'category_id' => $service['category_id'],
                    'duration_min' => $service['duration_min'],
                    'price' => $service['price'],
                    'is_active' => 1,
                    'sort_order' => $service['sort_order'],
                ]);
            }
        }
    }

    public function safeDown()
    {
        $this->delete('{{%bookings}}');
        $this->delete('{{%services}}');

        $this->delete('{{%service_categories}}', ['slug' => 'lashes-brows']);
        $this->delete('{{%service_categories}}', ['slug' => 'waxing']);

        // Re-insert original placeholder services
        $services = [
            ['master_id' => 1, 'name' => 'Haircut — Women', 'category_id' => 1, 'duration_min' => 60, 'price' => 2500, 'sort_order' => 1],
            ['master_id' => 1, 'name' => 'Haircut — Men', 'category_id' => 1, 'duration_min' => 30, 'price' => 1500, 'sort_order' => 2],
            ['master_id' => 1, 'name' => 'Hair Coloring', 'category_id' => 2, 'duration_min' => 120, 'price' => 5000, 'sort_order' => 3],
            ['master_id' => 1, 'name' => 'Blowout', 'category_id' => 3, 'duration_min' => 45, 'price' => 1800, 'sort_order' => 4],
            ['master_id' => 2, 'name' => 'Classic Manicure', 'category_id' => 4, 'duration_min' => 60, 'price' => 1500, 'sort_order' => 1],
            ['master_id' => 2, 'name' => 'Gel Manicure', 'category_id' => 4, 'duration_min' => 90, 'price' => 2500, 'sort_order' => 2],
            ['master_id' => 2, 'name' => 'Nail Art', 'category_id' => 4, 'duration_min' => 120, 'price' => 3500, 'sort_order' => 3],
            ['master_id' => 2, 'name' => 'Pedicure', 'category_id' => 4, 'duration_min' => 75, 'price' => 2000, 'sort_order' => 4],
            ['master_id' => 3, 'name' => 'Facial Cleansing', 'category_id' => 5, 'duration_min' => 60, 'price' => 3000, 'sort_order' => 1],
            ['master_id' => 3, 'name' => 'Chemical Peel', 'category_id' => 5, 'duration_min' => 45, 'price' => 4000, 'sort_order' => 2],
            ['master_id' => 3, 'name' => 'Anti-Aging Treatment', 'category_id' => 5, 'duration_min' => 90, 'price' => 6000, 'sort_order' => 3],
        ];

        foreach ($services as $service) {
            $service['is_active'] = 1;
            $this->insert('{{%services}}', $service);
        }
    }
}
