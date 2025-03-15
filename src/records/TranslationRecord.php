<?php

namespace eronax\translate\records;

use craft\db\ActiveRecord;
use eronax\translate\base\Table;

class TranslationRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return Table::$TABLE_TRANSLATIONS;
    }
}