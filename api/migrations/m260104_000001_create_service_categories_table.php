<?php

use yii\db\Migration;

/**
 * Create service_categories reference table.
 * Replaces free-text `services.category` with a proper FK reference.
 */
class m260104_000001_create_service_categories_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        // 1. Create reference table
        $this->createTable('{{%service_categories}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(191)->notNull()->unique(),
            'slug' => $this->string(191)->notNull()->unique(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // 2. Seed categories from existing data
        $categories = [
            ['name' => 'Стрижки', 'slug' => 'haircut', 'sort_order' => 1],
            ['name' => 'Окрашивание', 'slug' => 'coloring', 'sort_order' => 2],
            ['name' => 'Укладки', 'slug' => 'styling', 'sort_order' => 3],
            ['name' => 'Ногтевой сервис', 'slug' => 'nails', 'sort_order' => 4],
            ['name' => 'Уход за кожей', 'slug' => 'skincare', 'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            $this->insert('{{%service_categories}}', $cat);
        }

        // 3. Add category_id column to services
        $this->addColumn('{{%services}}', 'category_id', $this->integer()->unsigned()->null()->after('description'));

        // 4. Migrate existing text category → category_id
        $slugMap = [
            'haircut' => 1,
            'coloring' => 2,
            'styling' => 3,
            'nails' => 4,
            'skincare' => 5,
        ];

        foreach ($slugMap as $slug => $id) {
            $this->update('{{%services}}', ['category_id' => $id], ['category' => $slug]);
        }

        // 5. Drop old text column and index
        $this->dropIndex('idx_services_category', '{{%services}}');
        $this->dropColumn('{{%services}}', 'category');

        // 6. Add FK and index for category_id
        $this->createIndex('idx_services_category_id', '{{%services}}', ['category_id']);
        $this->addForeignKey(
            'fk_services_category_id',
            '{{%services}}',
            'category_id',
            '{{%service_categories}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        // Restore text column
        $this->dropForeignKey('fk_services_category_id', '{{%services}}');
        $this->dropIndex('idx_services_category_id', '{{%services}}');

        $this->addColumn('{{%services}}', 'category', $this->string(100)->null()->after('description'));

        // Restore text values from category_id
        $rows = (new \yii\db\Query())
            ->select(['id', 'slug'])
            ->from('{{%service_categories}}')
            ->all();

        foreach ($rows as $row) {
            $this->update('{{%services}}', ['category' => $row['slug']], ['category_id' => $row['id']]);
        }

        $this->dropColumn('{{%services}}', 'category_id');
        $this->createIndex('idx_services_category', '{{%services}}', ['category']);

        $this->dropTable('{{%service_categories}}');
    }
}
