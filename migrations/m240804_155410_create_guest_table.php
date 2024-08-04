<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%guest}}`.
 */
class m240804_155410_create_guest_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%guest}}', [
            'id' => $this->primaryKey(),
            'first_name' => $this->string(30)->notNull(),
            'last_name' => $this->string(30)->notNull(),
            'email' => $this->string()->unique(),
            'phone' => $this->string(15)->notNull()->unique(),
            'country' => $this->string(30),
            'created_at' => $this->dateTime()->defaultExpression('NOW()'),
            'updated_at' => $this->dateTime()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('{{%idx-guest-country}}', '{{%guest}}', 'country');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%guest}}');
    }
}
