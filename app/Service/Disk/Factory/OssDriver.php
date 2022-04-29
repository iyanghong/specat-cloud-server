<?php

namespace App\Service\Disk\Factory;

use App\Service\Disk\Config\DiskConfig;
use Illuminate\Http\UploadedFile;
use OSS\OssClient;

/**
 * 孤鸿渺影
 * 2022/4/18 15:24
 * OssDriver
 */
class OssDriver implements DiskFactoryInterface
{

    private OssClient $ossClient;
    private DiskConfig $config;
    private string $message;
    private string $path;
    private string $type = '';

    public function __construct($config)
    {

        $this->config = $config;
//        var_dump($this->config->toArray());
//        $this->config->setAccessKeyId('LTAI5tBhQCF55AbB56vNVunR');
//        $this->config->setAccessKeySecret('WQEWCHGGlx7ngJ1NcbcksAoaqmBGIc');
//        $this->config->setNode('oss-cn-shenzhen');
//        $this->config->setBucket('i-ts');
//        $this->config->setBasePath('test-cloud');
//        $this->config->setMaxSize(1024);
    }

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
    public function upload(UploadedFile $file, string $diyPath = '', array $option = []): bool
    {
        // TODO: Implement upload() method.

        $fileExtension = $file->getClientOriginalExtension();
        $size = filesize($file->getRealPath()) / 1024;
        if ($size > $this->config->getMaxSize()) {
            $this->message = '大小不能超过' . round($this->config->getMaxSize() / 1024, 2) . "MB";
            return false;
        }
        $this->resloveFileType($fileExtension);
        $realPath = $file->getRealPath();
        $this->path = $diyPath ? $diyPath : $this->generateResourcePath($fileExtension);
        if (!$this->initClient()) return false;
        try {
            $flag = $this->ossClient->uploadFile($this->config->getBucket(), $this->path, $realPath);
            if ($flag) {
                $this->message = '上传成功';
                unlink($realPath);
                return true;
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            $this->message = $e->getMessage() || '未知错误';
            return false;
        }
        return false;

    }

    public function generateResourcePath($fileExtension)
    {
        $path = $this->config->getBasePath() . '/' . date('Y/m/d') . '/' . getUuid() . '.' . $fileExtension;
        return $path;
    }

    public function resloveFileType($fileExtension)
    {
        $data = [
            'image' => array('webp', 'jpg', 'png', 'ico', 'bmp', 'gif', 'tif', 'pcx', 'tga', 'bmp', 'pxc', 'tiff', 'jpeg', 'exif', 'fpx', 'svg', 'psd', 'cdr', 'pcd', 'dxf', 'ufo', 'eps', 'ai', 'hdri'),
            'video' => array('mp4', 'avi', '3gp', 'rmvb', 'gif', 'wmv', 'mkv', 'mpg', 'vob', 'mov', 'flv', 'swf', 'mp3', 'ape', 'wma', 'aac', 'mmf', 'amr', 'm4a', 'm4r', 'ogg', 'wav', 'wavpack'),
            'zip' => array('rar', 'zip', 'tar', 'cab', 'uue', 'jar', 'iso', 'z', '7-zip', 'ace', 'lzh', 'arj', 'gzip', 'bz2', 'tz'),
            'text' => array('exe', 'doc', 'ppt', 'xls', 'wps', 'txt', 'lrc', 'wfs', 'torrent', 'html', 'htm', 'java', 'js', 'css', 'less', 'php', 'pdf', 'pps', 'host', 'box', 'docx', 'word', 'perfect', 'dot', 'dsf', 'efe', 'ini', 'json', 'lnk', 'log', 'msi', 'ost', 'pcs', 'tmp', 'xlsb'),
        ];
        foreach ($data as $key => $value) {
            if (is_array($value) && in_array($fileExtension, $value)) {
                $this->type = $key;
                return $key;
            } elseif ($value == $fileExtension) {
                $this->type = $key;
                return $key;
            }
        }
        $this->type = 'unknown';
        return 'unknown';
    }

    public function getFileType(): string
    {
        return $this->type;
    }

    public function copy(string $oldPath, string $newPath): bool
    {
        // TODO: Implement copy() method.
        if ($this->initClient() == false || $this->exist($oldPath) == false) {
            return false;
        }
        $flag = $this->ossClient->copyObject($this->config->getBucket(), $oldPath, $this->config->getBucket(), $newPath);
        if ($flag) {
            $this->message = '复制成功';
            return true;
        }
        $this->message = '复制失败';
        return false;
    }

    public function exist(string $path): bool
    {
        // TODO: Implement exist() method.
        if ($this->initClient() == false) {
            return false;
        }
        $flag = $this->ossClient->doesObjectExist($this->config->getBucket(), $path);
        if ($flag == false) {
            $this->message = '资源不存在';
            return false;
        }
        $this->message = '资源存在';
        return true;
    }

    public function move(string $oldPath, string $newPath): bool
    {
        // TODO: Implement move() method.
        if ($oldPath == $newPath) {
            $this->message = '旧路径与新路径不能相同';
            return false;
        }
        if ($this->copy($oldPath, $newPath) == true) {
            if ($this->delete($oldPath) == true) {
                $this->message = '移动成功';
                return true;
            }
        }
        $this->message = '移动失败';
        return false;
    }

    public function delete(string $path): bool
    {
        // TODO: Implement delete() method.
        if ($this->initClient() == false || $this->exist($path) == false) {
            return false;
        }

        $flag = $this->ossClient->deleteObject($this->config->getBucket(), $path);
        if ($flag) {
            return true;
        }
        $this->message = '删除失败';
        return false;
    }

    public function getPath(): string
    {
        // TODO: Implement getPath() method.
        return $this->path;
    }

    public function getMeta(string $path): array
    {
        // TODO: Implement getMeta() method.
    }

    public function getSize(string $path): int
    {
        // TODO: Implement getSize() method.
    }

    public function getResourceList(string $path): array
    {
        // TODO: Implement getResourceList() method.
    }

    public function getMessage(): string
    {
        // TODO: Implement getMsg() method.
        return $this->message;
    }

    public function initClient(): bool
    {
        if (empty($this->ossClient)) {
            try {
                $endpoint = 'http://oss-cn-' . $this->config->getNode() . '.aliyuncs.com';
                $this->ossClient = new OssClient($this->config->getAccessKeyId(), $this->config->getAccessKeySecret(), 'http://' . $endpoint);
                return true;
            } catch (\Exception $e) {
                $this->msg = '初始化资源库失败';
                return false;
            }
        }
        return true;
    }
}
