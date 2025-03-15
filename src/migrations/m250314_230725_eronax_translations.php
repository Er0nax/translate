<?php

namespace eronax\translate\migrations;

use Craft;
use craft\db\Migration;
use eronax\translate\base\Table;

/**
 * m250314_230725_eronax_translations migration.
 */
class m250314_230725_eronax_translations extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableName = Table::$TABLE_TRANSLATIONS;

        if (!Craft::$app->db->schema->getTableSchema($tableName)) {
            $this->createTable($tableName, [
                'id'          => $this->primaryKey(),
                'value'       => $this->text()->notNull(),
                'category'    => $this->string()->notNull()->defaultValue('app'),
                'de'          => $this->text(),
                'en'          => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
            ]);

            $this->createIndex(null, $tableName, ['value', 'category'], true);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(Table::$TABLE_TRANSLATIONS);
        return true;
    }
}
