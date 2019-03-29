<?php

namespace App\Models;

trait TableName
{
    /**
     * The name of table.
     *
     * @var string
     */
    protected static $tableName;

    /**
     * Get the name of table.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        if (!static::$tableName) {
        	static::$tableName = (new static())->getTable();
        }

        return static::$tableName;
    }
}
