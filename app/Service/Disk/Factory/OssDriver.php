<?php

namespace App\Service\Disk\Factory;

use App\Service\Disk\Config\DiskConfig;
use App\Service\Disk\Library\MultipartUpload;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redis;
use OSS\Core\OssException;
use OSS\Core\OssUtil;
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
    private string $message = '';
    private string $path;
    private string $type = '';
    private MultipartUpload $multipartUpload;

    public function __construct($config)
    {

        $this->config = $config;
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
        $this->resolveFileType($fileExtension);
        $realPath = $file->getRealPath();

        /** 重构图片大小 */
        if (isset($option['resize']) && is_array($option['resize'])) {
            if (is_numeric($option['resize']['width']) && is_numeric($option['resize']['height'])) {
                $img = $this->resizeImage($fileExtension, $file->getRealPath(), $option['resize']['width'], $option['resize']['height']);
                if ($img) {
                    unlink($realPath);
                    $realPath = $img;
                }
            }
        }

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
            $this->message = $e->getMessage() ? $e->getMessage() : '未知错误';
            return false;
        }
        return false;

    }

    /**
     * @throws OssException
     */
    public function initMultiUploadFile($path): ?MultipartUpload
    {
        if (!$this->initClient()) return null;
        $this->multipartUpload = new MultipartUpload($this->ossClient, $this->config, $path);
        return $this->multipartUpload;
    }

    /**
     *
     * @date : 2022/5/9 23:01
     * @param UploadedFile $file
     * @param $uploadId
     * @param $current
     * @param array $data
     * @return bool
     * @throws OssException
     * @author : 孤鸿渺影
     */
    public function multiUploadFile(UploadedFile $file,$uploadId, $current, array $data = []): bool
    {
        if (!$this->initClient()) return false;
        $realPath = $file->getRealPath();
        $this->path = $data['path'];

        $this->multipartUpload = new MultipartUpload($this->ossClient, $this->config, $this->path,$uploadId);
        $upload = $this->multipartUpload->upload($realPath, $current);
        $this->message = $this->multipartUpload->getMessage();
        return $upload;
    }

    /**
     *
     * @date : 2022/5/10 22:18
     * @return bool
     * @author : 孤鸿渺影
     */
    public function completeMultipartUpload(): bool
    {
        if (!$this->initClient()) return false;
        if (empty($this->multipartUpload)) {
            $this->message = '分片上传程序未初始化';
            return false;
        }
        return $this->multipartUpload->merge();
    }

    /**
     * 生成资源路径
     * @date : 2022/5/1 19:15
     * @param $fileExtension
     * @return string
     * @author : 孤鸿渺影
     */
    public function generateResourcePath($fileExtension): string
    {
        return $this->config->getBasePath() . '/' . date('Y/m/d') . '/' . getUuid() . '.' . $fileExtension;
    }

    /**
     * 解析文件类型
     * @date : 2022/5/1 19:15
     * @param $fileExtension
     * @return string
     * @author : 孤鸿渺影
     */
    public function resolveFileType($fileExtension): string
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

    /**
     *
     * @date : 2022/5/1 19:21
     * @param $ext
     * @param $tmpName
     * @param $xMax
     * @param $yMax
     * @return false|\GdImage|resource|string
     * @author : 孤鸿渺影
     */
    public function resizeImage($ext, $tmpName, $xMax, $yMax)
    {
        if ($ext == "jpg" || $ext == "jpeg")
            $im = imagecreatefromjpeg($tmpName);
        elseif ($ext == "png")
            $im = imagecreatefrompng($tmpName);
        elseif ($ext == "gif")
            $im = imagecreatefromgif($tmpName);
        if (!$im) {
            return false;
        }
        $x = imagesx($im);
        $y = imagesy($im);

        if ($x <= $xMax && $y <= $yMax)
            return $im;

        if ($x >= $y) {
            $newX = $xMax;
            $newY = $newX * $y / $x;
        } else {
            $newY = $yMax;
            $newX = $x / $y * $newY;
        }
        $im2 = imagecreatetruecolor($newX, $newY);
        imagecopyresized($im2, $im, 0, 0, 0, 0, floor($newX), floor($newY), $x, $y);

        $fileName = "${$tmpName}-1.${ext}";
        if ($ext == "jpg" || $ext == "jpeg") {
            imagejpeg($im2, $fileName);
        } elseif ($ext == "png") {
            imagepng($im2, $fileName);
        } elseif ($ext == "gif") {
            imagegif($im2, $fileName);
        }
        return $fileName;
    }

    /**
     * 获取文件类型
     * @date : 2022/5/1 19:17
     * @return string
     * @author : 孤鸿渺影
     */
    public function getFileType(): string
    {
        return $this->type;
    }

    /**
     * 复制资源
     * @date : 2022/5/1 19:17
     * @param string $oldPath
     * @param string $newPath
     * @return bool
     * @throws \OSS\Core\OssException
     * @author : 孤鸿渺影
     */
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

    /**
     * 判断资源是否存在
     * @date : 2022/5/1 19:17
     * @param string $path
     * @return bool
     * @author : 孤鸿渺影
     */
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

    /**
     * 资源移动
     * @date : 2022/5/1 19:18
     * @param string $oldPath
     * @param string $newPath
     * @return bool
     * @throws \OSS\Core\OssException
     * @author : 孤鸿渺影
     */
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

    /**
     * 资源删除
     * @date : 2022/5/1 19:18
     * @param string $path
     * @return bool
     * @author : 孤鸿渺影
     */
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

    /**
     * @return MultipartUpload
     */
    public function getMultipartUpload(): MultipartUpload
    {
        return $this->multipartUpload;
    }

    /**
     * @param MultipartUpload $multipartUpload
     */
    public function setMultipartUpload(MultipartUpload $multipartUpload): void
    {
        $this->multipartUpload = $multipartUpload;
    }


}
