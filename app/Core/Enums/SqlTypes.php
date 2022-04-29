<?php


namespace App\Core\Enums;


use xiaolin\Enum\Enum;

/**
 * SqlTypes
 * @method static getMessage(string $value)
 * @date : 2022/4/24 18:46
 * @author : 孤鸿渺影
 */
class SqlTypes extends Enum
{

    /**
     * @Message('integer')
     */
    public static $ENUM_SQL_TYPES_TINYINT = 'tinyint';

    /**
     * @Message('integer')
     */
    public static $ENUM_SQL_TYPES_SMALLINT = 'smallint';

    /**
     * @Message('integer')
     */
    public static $ENUM_SQL_TYPES_MEDIUMINT = 'mediumint';

    /**
     * @Message('integer')
     */
    public static $ENUM_SQL_TYPES_INTEGER = 'integer';

    /**
     * @Message('integer')
     */
    public static $ENUM_SQL_TYPES_BIGINT = 'bigint';

    /**
     * @Message('integer')
     */
    public static $ENUM_SQL_TYPES_INT = 'int';

    /**
     * @Message('integer')
     */
    public static $ENUM_SQL_TYPES_TIMESTAMP = 'timestamp';


    /**
     * @Message('float')
     */
    public static $ENUM_SQL_TYPES_FLOAT = 'float';

    /**
     * @Message('float')
     */
    public static $ENUM_SQL_TYPES_DOUBLE = 'double';

    /**
     * @Message('float')
     */
    public static $ENUM_SQL_TYPES_DECIMAL = 'decimal';

    /**
     * @Message('datetime')
     */
    public static $ENUM_SQL_TYPES_DATETIME = 'datetime';

    /**
     * @Message('date')
     */
    public static $ENUM_SQL_TYPES_DATE = 'date';

    /**
     * @Message('integer')
     */
    public static $ENUM_SQL_TYPES_TIME = 'time';

    /**
     * @Message('integer')
     */
    public static $ENUM_SQL_TYPES_YEAR = 'year';

    /**
     * @Message('string')
     */
    public static $ENUM_SQL_TYPES_CHAR = 'char';

    /**
     * @Message('string')
     */
    public static $ENUM_SQL_TYPES_VARCHAR = 'varchar';

    /**
     * @Message('string')
     */
    public static $ENUM_SQL_TYPES_TINYBLOB = 'tinyblob';

    /**
     * @Message('string')
     */
    public static $ENUM_SQL_TYPES_TINYTEXT = 'tinytext';

    /**
     * @Message('string')
     */
    public static $ENUM_SQL_TYPES_BLOB = 'BLOB';

    /**
     * @Message('string')
     */
    public static $ENUM_SQL_TYPES_TEXT = 'text';

    /**
     * @Message('string')
     */
    public static $ENUM_SQL_TYPES_MEDIUMBLOB = 'mediumblob';

    /**
     * @Message('string')
     */
    public static $ENUM_SQL_TYPES_MEDIUMTEXT = 'mediumtext';

    /**
     * @Message('string')
     */
    public static $ENUM_SQL_TYPES_LONGBLOB = 'longblob';

    /**
     * @Message('string')
     */
    public static $ENUM_SQL_TYPES_LONGTEXT = 'longtext';

}
