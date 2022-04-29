<?php

namespace App\Service\Disk;

use xiaolin\Enum\Enum;

/**
 * 孤鸿渺影
 * 2022/4/24 17:45
 * DiskNodeEnum
 * @method static getMessage(string $value)
 */
class DiskNodeEnum extends Enum
{
    /**
     * @Message('深圳')
     */
    public static $ENUM_SHEN_ZHEN = 'shenzhen';

    /**
     * @Message('广州')
     */
    public static $ENUM_GUANG_ZHOU = 'guangzhou';

}
