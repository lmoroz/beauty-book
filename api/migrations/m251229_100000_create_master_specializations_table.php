<?php

use yii\db\Migration;

class m251229_100000_create_master_specializations_table extends Migration
{
    private $tableOptions = 'ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp()
    {
        $this->createTable('{{%master_specializations}}', [
            'master_id' => $this->integer()->unsigned()->notNull(),
            'specialization_id' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->addPrimaryKey(
            'pk_master_specializations',
            '{{%master_specializations}}',
            ['master_id', 'specialization_id']
        );

        $this->addForeignKey(
            'fk_master_spec_master_id',
            '{{%master_specializations}}',
            'master_id',
            '{{%masters}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_master_spec_specialization_id',
            '{{%master_specializations}}',
            'specialization_id',
            '{{%specializations}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Migrate existing data from specialization_id column
        $this->execute('
            INSERT INTO {{%master_specializations}} (master_id, specialization_id)
            SELECT id, specialization_id
            FROM {{%masters}}
            WHERE specialization_id IS NOT NULL
        ');

        // Drop old FK and column
        $this->dropForeignKey('fk_masters_specialization_id', '{{%masters}}');
        $this->dropColumn('{{%masters}}', 'specialization_id');
    }

    public function safeDown()
    {
        $this->addColumn('{{%masters}}', 'specialization_id', $this->integer()->null()->after('slug'));

        // Restore first specialization per master
        $this->execute('
            UPDATE {{%masters}} m
            JOIN (
                SELECT master_id, MIN(specialization_id) AS spec_id
                FROM {{%master_specializations}}
                GROUP BY master_id
            ) ms ON ms.master_id = m.id
            SET m.specialization_id = ms.spec_id
        ');

        $this->addForeignKey(
            'fk_masters_specialization_id',
            '{{%masters}}',
            'specialization_id',
            '{{%specializations}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->dropForeignKey('fk_master_spec_specialization_id', '{{%master_specializations}}');
        $this->dropForeignKey('fk_master_spec_master_id', '{{%master_specializations}}');
        $this->dropTable('{{%master_specializations}}');
    }
}
