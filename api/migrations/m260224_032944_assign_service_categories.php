<?php

use yii\db\Migration;

/**
 * Assign categories to existing services based on master specialization.
 */
class m260224_032944_assign_service_categories extends Migration
{
    public function safeUp()
    {
        // Map: master_id => category_id based on known data
        // Master 1 (Anna) = hairdresser → haircut(1), coloring(2), styling(3)
        // Master 2 (Maria) = manicurist → nails(4)
        // Master 3 (Elena) = cosmetologist → skincare(5)

        // Assign all services of master 1 that contain styling-related keywords
        $this->execute("UPDATE {{%services}} SET category_id = 1 WHERE master_id = 1 AND (name LIKE '%стрижк%' OR name LIKE '%Haircut%')");
        $this->execute("UPDATE {{%services}} SET category_id = 2 WHERE master_id = 1 AND (name LIKE '%окрашив%' OR name LIKE '%Color%')");
        $this->execute("UPDATE {{%services}} SET category_id = 3 WHERE master_id = 1 AND (name LIKE '%уклад%' OR name LIKE '%Blowout%' OR name LIKE '%уход%' OR name LIKE '%счастье%')");

        // All of Maria's services → nails
        $this->execute("UPDATE {{%services}} SET category_id = 4 WHERE master_id = 2 AND category_id IS NULL");

        // All of Elena's services → skincare
        $this->execute("UPDATE {{%services}} SET category_id = 5 WHERE master_id = 3 AND category_id IS NULL");

        // Catch any remaining unassigned for master 1
        $this->execute("UPDATE {{%services}} SET category_id = 1 WHERE master_id = 1 AND category_id IS NULL");
    }

    public function safeDown()
    {
        $this->execute("UPDATE {{%services}} SET category_id = NULL");
    }
}
