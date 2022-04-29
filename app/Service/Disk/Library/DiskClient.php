<?php


namespace App\Service\Disk\Library;


use Exception;

interface DiskClient
{
    /**
     * @param string $bucket 存储桶
     * @param string $path 上传存储路径
     * @param string $file  文件原始路径
     * @param $option 参数
     * @return mixed
     * @throws Exception
     */
    public function putObject(string $bucket,string $path,string $file,$option);

    public function upload(string $bucket,string $path,string $file,$option);

    /**
     * 复制对象
     * @param string $bucket 存储桶
     * @param string $from 路径
     * @param string $to 新路径
     * @return mixed
     */
    public function copyObject(string $bucket,string $from,string $to);

    /**
     * 移动对象
     * @param string $bucket 存储桶
     * @param string $from 路径
     * @param string $to 新路径
     * @return mixed
     */
    public function moveObject(string $bucket,string $from,string $to);

    /**
     * 删除单个对象
     * @param string $bucket 存储桶
     * @param string $path 路径
     * @return mixed
     */
    public function deleteObject(string $bucket,string $path);

    /**
     * 删除多个对象
     * @param string $bucket 存储桶
     * @param array $pathList
     * @return mixed
     */
    public function deleteObjects(string $bucket,array $pathList);
}
