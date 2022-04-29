<?php

namespace App\Service\Disk\Factory;

use Illuminate\Http\UploadedFile;

interface DiskFactoryInterface
{
    /**
     * @Notes:上传文件
     * @Interface upload
     * @param UploadedFile $file 文件
     * @param string $diyPath 自定义目录
     * @param array $option
     * @return bool
     * @Author: TS
     * @Time: 2020-06-24   21:29
     */
    public function upload(UploadedFile $file, string $diyPath = '', array $option = []): bool;

    public function getFileType(): string;

    /**
     * @Notes:复制文件
     * @Interface copy
     * @param string $oldPath
     * @param string $newPath
     * @return bool
     * @Author: TS
     * @Time: 2020-06-24   21:12
     */
    public function copy(string $oldPath, string $newPath): bool;


    /**
     * @Notes:检查资源是否存在
     * @Interface exist
     * @param string $path
     * @return bool
     * @Author: TS
     * @Time: 2020-06-24   21:19
     */
    public function exist(string $path): bool;

    /**
     * @Notes: 移动文件
     * @Interface move
     * @param string $oldPath
     * @param string $newPath
     * @return bool
     * @Author: TS
     * @Time: 2020-06-23   17:33
     */
    public function move(string $oldPath, string $newPath): bool;


    /**
     * @Notes:删除资源
     * @Interface delete
     * @param string $path 资源路径
     * @return bool
     * @Author: TS
     * @Time: 2020-06-24   21:27
     */
    public function delete(string $path): bool;


    /**
     * 获取上传文件路径
     * @return mixed
     */
    public function getPath(): string;


    /**
     * @Notes:获取文件信息
     * @Interface getMeta
     * @param string $path
     * @return array
     * @Author: TS
     * @Time: 2020-06-24   21:31
     */
    public function getMeta(string $path): array;


    /**
     * Implement getMeta()
     * @Notes:获取文件大小
     * @Interface getSize
     * @param $path
     * @return int
     * @Author: TS
     * @Time: 2020-06-24   21:32
     */
    public function getSize(string $path): int;

    /**
     * @Notes:获取资源列表
     * @Interface getResourceList
     * @param string $path
     * @return array
     * @Author: TS
     * @Time: 2020-06-24   21:58
     */
    public function getResourceList(string $path): array;

    /**
     * 获取信息
     * @return mixed
     */
    public function getMessage(): string;
}
