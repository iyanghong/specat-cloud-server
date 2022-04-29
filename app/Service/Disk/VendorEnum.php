<?php

namespace App\Service\Disk;

use xiaolin\Enum\Enum;

/**
 * 孤鸿渺影
 * 2022/4/23 23:13
 * VendorEnum
 * @method static getMessage(string $value)
 */
class VendorEnum extends Enum
{
    /**
     * @Message('系统默认硬盘')
     */
    public static $ENUM_SYSTEM_DEFAULT = -1;
    /**
     * @Message('阿里云OSS')
     */
    public static $ENUM_ALIYUN_OSS = 'aliyun-oss';

}
