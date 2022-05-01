<?php

namespace App\Service\Disk;

use App\Service\Disk\Config\DiskConfig;
use App\Service\Disk\Factory\DiskFactoryInterface;
use App\Service\Disk\Factory\OssDriver;

/**
 * 孤鸿渺影
 * 2022/4/18 15:19
 * DiskFactory
 */
class DiskFactory
{
    private static array $factoryList = [
        'aliyun-oss' => OssDriver::class
    ];

    /**
     *
     * @date : 2022/4/18 15:51
     * @param $type
     * @return DiskFactoryInterface|null
     * @author : 孤鸿渺影
     */
    public static function build(DiskConfig $diskConfig): ?DiskFactoryInterface
    {
        if (empty(DiskFactory::$factoryList[$diskConfig->getVendor()])) return null;
        /** @var  $disk DiskFactoryInterface */
        return new DiskFactory::$factoryList[$diskConfig->getVendor()]($diskConfig);
    }

    public static function resolveFileType($fileExtension): string
    {
        $data = [
            'image' => array('webp', 'jpg', 'png', 'ico', 'bmp', 'gif', 'tif', 'pcx', 'tga', 'bmp', 'pxc', 'tiff', 'jpeg', 'exif', 'fpx', 'svg', 'psd', 'cdr', 'pcd', 'dxf', 'ufo', 'eps', 'ai', 'hdri'),
            'video' => array('mp4', 'avi', '3gp', 'rmvb', 'gif', 'wmv', 'mkv', 'mpg', 'vob', 'mov', 'flv', 'swf', 'mp3', 'ape', 'wma', 'aac', 'mmf', 'amr', 'm4a', 'm4r', 'ogg', 'wav', 'wavpack'),
            'zip' => array('rar', 'zip', 'tar', 'cab', 'uue', 'jar', 'iso', 'z', '7-zip', 'ace', 'lzh', 'arj', 'gzip', 'bz2', 'tz'),
            'text' => array('exe', 'doc', 'ppt', 'xls', 'wps', 'txt', 'lrc', 'wfs', 'torrent', 'html', 'htm', 'java', 'js', 'css', 'less', 'php', 'pdf', 'pps', 'host', 'box', 'docx', 'word', 'perfect', 'dot', 'dsf', 'efe', 'ini', 'json', 'lnk', 'log', 'msi', 'ost', 'pcs', 'tmp', 'xlsb'),
        ];
        foreach ($data as $key => $value) {
            if (is_array($value) && in_array($fileExtension, $value)) {
                return $key;
            } elseif ($value == $fileExtension) {

                return $key;
            }
        }
        return 'unknown';
    }

}
