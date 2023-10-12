<?php

use yii\db\Migration;

/**
 * Class m231011_132533_apple_table
 */
class m231011_132533_apple_table extends Migration
{

    const TABLE_NAME = 'apple';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $fields = [
            'id' => 'int(11) PRIMARY KEY AUTO_INCREMENT',
            'color' => "enum('red','yellow','green','white') NOT NULL",
            'created_at' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()',
            'dropped_at' => 'TIMESTAMP NULL DEFAULT NULL',
            'status' => 'SMALLINT NOT NULL DEFAULT 0',
            'size' => 'SMALLINT NOT NULL DEFAULT 100'
        ];
        $this->createTable(self::TABLE_NAME, $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
