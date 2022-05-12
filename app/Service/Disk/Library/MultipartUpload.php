<?php

namespace App\Service\Disk\Library;

use App\Service\Disk\Config\DiskConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\OssClient;

/**
 * 孤鸿渺影
 * 2022/5/10 21:02
 * MultipartUpload
 */
class MultipartUpload
{
    private string $uploadId = '';
    private string $cacheKey = '';
    private OssClient $ossClient;
    private DiskConfig $config;
    private string $path;
    private static int $MULTI_LENGTH = 1024 * 1024 * 4;
    private string $message = '';
    private int $index;

    public array $uploadParts = array();

    /**
     * @param $client
     * @param DiskConfig $config
     * @param $path
     * @param string $uploadId
     * @throws OssException
     */
    public function __construct($client, DiskConfig $config, $path, string $uploadId = '')
    {
        $this->config = $config;
        $this->path = $path;
        $this->cacheKey = 'MultiUpload:Parts:' . onlineMember()->getId() . ':' . md5($path);
        $this->ossClient = $client;
        $this->uploadId = !empty($uploadId) ? $uploadId : $this->ossClient->initiateMultipartUpload($this->config->getBucket(), $this->path);
    }


    public function upload($file, $current): bool
    {
        if (empty($this->uploadId)) {
            $this->message = '上传ID错误';
            return false;
        }
        $uploadFileSize = filesize($file);
        $partSize = systemConfig()->get('Cloud.MultiBlockUploadSize');
        if($partSize){
            $partSize = $partSize * 1024 * 1024;
        }else{
            $partSize = self::$MULTI_LENGTH;
        }
        $pieces = $this->ossClient->generateMultiuploadParts($uploadFileSize, $partSize);
        $uploadPosition = 0;
        $isCheckMd5 = true;
        foreach ($pieces as $i => $piece) {
            $fromPos = $uploadPosition + (integer)$piece[$this->ossClient::OSS_SEEK_TO];
            $toPos = (integer)$piece[$this->ossClient::OSS_LENGTH] + $fromPos - 1;
            $upOptions = array(
                // 上传文件。
                $this->ossClient::OSS_FILE_UPLOAD => $file,
                // 设置分片号。
                $this->ossClient::OSS_PART_NUM => ($current),
                // 指定分片上传起始位置。
                $this->ossClient::OSS_SEEK_TO => $fromPos,
                // 指定文件长度。
                $this->ossClient::OSS_LENGTH => $toPos - $fromPos + 1,
                // 是否开启MD5校验，true为开启。
                $this->ossClient::OSS_CHECK_MD5 => $isCheckMd5,
            );
            // 开启MD5校验。
            if ($isCheckMd5) {
                $contentMd5 = OssUtil::getMd5SumForFile($file, $fromPos, $toPos);
                $upOptions[$this->ossClient::OSS_CONTENT_MD5] = $contentMd5;
            }
            try {
                $item = $this->ossClient->uploadPart($this->config->getBucket(), $this->path, $this->uploadId, $upOptions);
                //把分片存入有序集合
                Redis::zadd($this->cacheKey . ":uploadPart", $current, $item);
                // 上传分片。
            } catch (OssException $e) {
                $this->message = $e->getMessage() ? ($e->getMessage() . "initiateMultipartUpload, uploadPart - part#{$i} FAILED\n") : '未知错误';
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @date : 2022/5/10 22:13
     * @return bool
     * @author : 孤鸿渺影
     */
    public function merge(): bool
    {
        if (empty($this->uploadId)) {
            $this->message = '上传ID错误';
            return false;
        }

        $uploadPartCacheList = Redis::zrange($this->cacheKey . ":uploadPart", 0, -1);
        Redis::zremrangebyrank($this->cacheKey . ":uploadPart", 0, -1);
        $uploadParts = array();
        foreach ($uploadPartCacheList as $i => $eTag) {
            $uploadParts[] = array(
                'PartNumber' => ($i + 1),
                'ETag' => $eTag,
            );
        }
        $this->uploadParts = $uploadParts;
        /**
         * 步骤3：完成上传。
         */
        try {
            // 执行completeMultipartUpload操作时，需要提供所有有效的$uploadParts。OSS收到提交的$uploadParts后，会逐一验证每个分片的有效性。当所有的数据分片验证通过后，OSS将把这些分片组合成一个完整的文件。
            $flag = $this->ossClient->completeMultipartUpload($this->config->getBucket(), $this->path, $this->uploadId, $uploadParts);
            if (!$flag) {
                $this->message = '合并分片错误';
                return false;
            }
        } catch (OssException $e) {
            $this->message = $e->getMessage() ? ($e->getMessage() . "completeMultipartUpload FAILED") : '未知错误';
            return false;
        }
        return true;
    }

    /**
     * 获取已上传分块数量
     * @date : 2022/5/10 22:20
     * @return int
     * @author : 孤鸿渺影
     */
    public function getPartTotal(): int
    {
        $total = Redis::zcard($this->cacheKey . ":uploadPart");
        return $total ?? 0;
    }

    /**
     * @return string
     */
    public function getUploadId(): string
    {
        return $this->uploadId;
    }

    /**
     * @param mixed|string $uploadId
     */
    public function setUploadId(mixed $uploadId): void
    {
        $this->uploadId = $uploadId;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    /**
     * @param string $cacheKey
     */
    public function setCacheKey(string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }


}
