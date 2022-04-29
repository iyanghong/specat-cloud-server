<?php


namespace App\Service\Disk;


use Illuminate\Http\UploadedFile;

/**
 * Interface LocalDiskInterface
 * @package App\Service\Disk
 */
interface LocalDiskInterface
{

    /**
     * @Notes: 创建文件
     * @Interface mkFile
     * @param $path string 路径
     * @param $content string 内容
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:11
     */
    public function mkFile(string $path,string $content) : bool;

    /**
     * @Notes: 创建文件夹
     * @Interface mkDir
     * @param $path string 路径
     * @param $content
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:11
     */
    public function mkDir(string $path) :bool ;

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
    public function upload(UploadedFile $file, string $diyPath = '', array $option = []): bool;
    /**
     * @Notes: 获取文件内容
     * @Interface get
     * @param $path string 文件路径
     * @return null
     * @Author: TS
     * @Time: 2020-06-19   13:12
     */
    public function get($path) : ?string;

    /**
     * @Notes: 更新文件内容
     * @Interface update
     * @param $path string 文件路径
     * @param $content string 内容
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:13
     */
    public function update(string $path,string $content) :bool ;

    /**
     * @Notes: 移动目标
     * @Interface move
     * @param $path
     * @param $newpath
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:14
     */
    public function move(string $path,string $newpath) : bool ;
    /**
     * @Notes: 复制目标
     * @Interface copy
     * @param $path string 当前路径
     * @param $newpath string 复制路径
     * @return array
     * @Author: TS
     * @Time: 2020-06-19   13:14
     */
    public function copy(string $path,string $newpath) :bool ;

    /**
     * @Notes: 向文件追加内容
     * @Interface fill
     * @param $path string 路径
     * @param $content string 追加内容
     * @param int $type 追加方式 1 => 在后面追加内容 0 => 在前面追加内容
     * @return array
     * @Author: TS
     * @Time: 2020-06-19   13:16
     */
    public function fill(string $path,string $content,int $type = 1) :bool ;
    /**
     * @Notes: 删除目标
     * @Interface del
     * @param $path string 目标路径
     * @param int $type 目标类型 1 => 文件  0 => 目录
     * @return array
     * @Author: TS
     * @Time: 2020-06-19   13:17
     */
    public function del(string $path,int $type = 1):bool ;
    /**
     * 获取指定目录下的子目录与文件
     * @param string $directory 指定目录
     * @return array  res
     */
    public function catalog(string $directory = '/') : array ;
    /**
     * @Notes:  检查目标是否存在
     * @Interface check
     * @param $path string 路径
     * @param int $type 目标类型 1 => 文件 2 => 文件或目录
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:20
     */
    public function check(string $path, int $type = 1) :bool ;
    /**
     * @Notes:解压
     * @Interface decZip
     * @param $path
     * @param string $decPath
     * @return array
     * @Author: TS
     * @Time: 2020-06-19   13:21
     */
    public function decZip(string $path,string $decPath = '') : bool ;

    /**
     * @Notes: 压缩
     * @Interface enZip
     * @param $path
     * @param $filename
     * @param array $option
     * @return bool
     * @Author: TS
     * @Time: 2020-06-19   13:22
     */
    public function enZip(string $path,string $filename,array $option = []) :bool;

    /**
     * @Notes: 向压缩文件添加文件
     * @Interface addFileToZip
     * @param $path
     * @param $zip
     * @param $option
     * @Author: TS
     * @Time: 2020-06-19   13:23
     */
    public function addFileToZip(string $path,object $zip,array $option) : void;

    /**
     * @Notes: 获取文件更新时间
     * @Interface getFileUpdateTime
     * @param $path
     * @return Carbon|int
     * @Author: TS
     * @Time: 2020-06-19   13:24
     */
    public function getFileUpdateTime(string $path) : ?string;

    /**
     * @Notes:下载文件
     * @Interface download
     * @param $url
     * @param string $save_dir
     * @param string $filename
     * @param int $type
     * @return array|bool
     * @Author: TS
     * @Time: 2020-06-19   13:25
     */
    public function download(string $url,string $save_dir = '',string $filename = '',int $type = 1) : ?bool;

    /**
     * @Notes:获取文件夹大小
     * @Interface getDirSize
     * @param $dir
     * @return false|int
     * @Author: TS
     * @Time: 2020-06-19   13:25
     */
    public function getDirSize(string $dir):int ;
    /**
     * @Notes:获取文件夹文件数量
     * @Interface getDicFileNum
     * @param $dir
     * @return int
     * @Author: TS
     * @Time: 2020-06-19   13:26
     */
    public function getDicFileNum(string $dir) : int ;

}