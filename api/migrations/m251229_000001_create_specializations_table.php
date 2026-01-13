<?php

use yii\db\Migration;

class m251229_000001_create_specializations_table extends Migration
{
    private $tableOptions = 'ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp()
    {
        $this->createTable('{{%specializations}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(191)->notNull()->unique(),
            'slug' => $this->string(191)->notNull()->unique(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $this->tableOptions);

        $defaults = [
            ['name' => 'Hairdresser', 'slug' => 'hairdresser', 'sort_order' => 1],
            ['name' => 'Manicurist', 'slug' => 'manicurist', 'sort_order' => 2],
            ['name' => 'Cosmetologist', 'slug' => 'cosmetologist', 'sort_order' => 3],
            ['name' => 'Massage', 'slug' => 'massage', 'sort_order' => 4],
            ['name' => 'Stylist', 'slug' => 'stylist', 'sort_order' => 5],
            ['name' => 'Other', 'slug' => 'other', 'sort_order' => 100],
        ];

        foreach ($defaults as $row) {
            $this->insert('{{%specializations}}', $row);
        }

        $this->addColumn('{{%masters}}', 'specialization_id', $this->integer()->null()->after('slug'));

        $this->execute('
            UPDATE {{%masters}} m
            JOIN {{%specializations}} s ON s.slug = m.specialization
            SET m.specialization_id = s.id
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

        $this->dropColumn('{{%masters}}', 'specialization');
    }

    public function safeDown()
    {
        $this->addColumn('{{%masters}}', 'specialization', $this->string(255)->null()->after('slug'));

        $this->execute('
            UPDATE {{%masters}} m
            JOIN {{%specializations}} s ON s.id = m.specialization_id
            SET m.specialization = s.slug
        ');

        $this->dropForeignKey('fk_masters_specialization_id', '{{%masters}}');
        $this->dropColumn('{{%masters}}', 'specialization_id');
        $this->dropTable('{{%specializations}}');
    }
}
