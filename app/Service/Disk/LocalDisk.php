<?php


namespace App\Service\Disk;


use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LocalDisk extends Disk implements LocalDiskInterface
{

    /**
     * 上传文件
     * @param UploadedFile $file 文件
     * @param string $diyPath 自定义目录
     * @param array $option
     * Date : 2021/5/6 22:39
     * Author : 孤鸿渺影
     * @return bool
     * @throws UploadException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function upload(UploadedFile $file, string $diyPath = '', array $option = []): bool
    {
        $uploadConfig = new UploadExceptConfig();
        $fileExtension = $file->getClientOriginalExtension();
        $type = $this->checkUploadExtension($fileExtension, $uploadConfig);
        if ($type == null) {
            throw  new UploadException('不支持此格式');
        }
        //忽略大小检测
        if (!isset($option['ignoreSize']) || $option['ignoreSize'] == false) {
            $size = filesize($file->getRealPath());
            if ($size > $uploadConfig->getTypeSizeList()[$type]) {
                throw  new UploadException('大小不能超过' . round($uploadConfig->getTypeSizeList()[$type] / 1024 / 1024, 2) . "MB");
            }
        }
        $pathTmp = empty($diyPath) ? $this->getResourcePath($type, $fileExtension) : $diyPath;
        $pathTmp = explode('/', $pathTmp);
        $fileName = end($pathTmp);
        $pathTmp = array_slice($pathTmp, 0, sizeof($pathTmp) - 1);
        $directory = implode('/', $pathTmp);
        $path = $file->move(base_path($directory), $fileName);
        return true;
    }

    /**
     * @Notes:验证是否是运行上传的数据类型
     * @Interface checkUploadExtension
     * @param string $fileExtension
     * @return string|null
     * @Author: TS
     * @Time: 2020-06-23   19:43
     */
    private function checkUploadExtension(string $fileExtension, UploadExceptConfig $uploadConfig): ?string
    {
        if (in_array($fileExtension, $uploadConfig->getExceptImages())) {
            return 'images';
        }
        if (in_array($fileExtension, $uploadConfig->getExceptMedia())) {
            return 'media';
        }
        if (in_array($fileExtension, $uploadConfig->getExceptPackage())) {
            return 'package';
        }
        if (in_array($fileExtension, $uploadConfig->getExceptText())) {
            return 'text';
        }
        return null;
    }

    /**
     * @Notes:获取资源目录
     * @Interface getResourcePath
     * @param string $type
     * @param string $fileExtension
     * @Author: TS
     * @Time: 2020-06-24   14:11
     */
    private function getResourcePath(string $type, string $fileExtension): string
    {
        $path = '';
        $mode = request()->input('type');

        $suffix = time() . '-' . getUuid() . '.' . $fileExtension;
        if ($mode == 'cache') {
            $path = 'cache/' . $type . '/' . $suffix;
        } else if ($mode == 'header') {
            $path = 'users/' . onlineMember()->getId() . '/data/header/' . time() . '.' . $fileExtension;
        } else {
            $path = 'users/' . onlineMember()->getId() . '/' . $type . '/' . $suffix;
        }

        return $path;
    }
}