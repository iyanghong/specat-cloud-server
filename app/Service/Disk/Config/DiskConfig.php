<?php

namespace App\Service\Disk\Config;

use JetBrains\PhpStorm\ArrayShape;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 孤鸿渺影
 * 2022/4/18 22:30
 * DiskConfig
 */
class DiskConfig
{
    /**
     * @var string 磁盘提供商
     */
    private string $vendor;
    /**
     * @var string 访问ID
     */
    private string $accessKeyId;
    /**
     * @var string 访问密钥
     */
    private string $accessKeySecret;
    /**
     * @var string 磁盘所属节点
     */
    private string $node;
    /**
     * @var string 磁盘根路径
     */
    private string $basePath;
    /**
     * @var bool 是否系统默认
     */
    private bool $default;

    private string $bucket;

    private string $access_path;

    private int $maxSize;

    /**
     * @param null $data
     * @throws InvalidArgumentException
     */
    public function __construct($data = null)
    {
        if ($data != null) {
            $this->default = $data['is_default'];
            if ($this->default) {
                $this->setDefaultDisk();
                if(!empty($data['base_path'])){
                    $basePath = trim(systemConfig()->get('Cloud.defaultDiskBasePath'),'/') . "/" . trim($data['base_path'],'/');
                    $this->basePath = $basePath;
                }
            } else {
                $this->vendor = $data['vendor'];
                $this->accessKeyId = maskCrypt()->decrypt($data['access_key_id']);
                $this->accessKeySecret = maskCrypt()->decrypt($data['access_key_secret']);
                $this->maxSize = $data['max_size'];
                $this->node = $data['node'];
                $this->bucket = $data['bucket'];
                $this->basePath = $data['base_path'];
                $this->accessPath = $data['access_path'];
            }
        }
    }

    /**
     *
     * @date : 2022/4/24 18:24
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function setDefaultDisk()
    {
        $this->vendor = systemConfig()->get('Cloud.defaultVendor');
        $this->accessKeyId = systemConfig()->get('Cloud.defaultDiskAccessKeyId');
        $this->accessKeySecret = systemConfig()->get('Cloud.defaultDiskAccessKeySecret');
        $this->node = systemConfig()->get('Cloud.defaultDiskNode');
        $this->basePath = trim(systemConfig()->get('Cloud.defaultDiskBasePath'),'/');
        $this->default = 1;
        $this->bucket = systemConfig()->get('Cloud.defaultDiskBucket');
        $this->accessPath = systemConfig()->get('Cloud.defaultDiskAccessPath');
        $this->maxSize = systemConfig()->get('Cloud.defaultDiskMaxSize');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setSystemDisk()
    {
        $this->vendor = 'aliyun-oss';
        $this->accessKeyId = systemConfig()->get('Sys.AccessKeyId');
        $this->accessKeySecret = systemConfig()->get('Sys.AccessKeySecret');
        $this->node = 'shenzhen';
        $this->basePath = '';
        $this->default = 1;
        $this->bucket = systemConfig()->get('Sys.AliyunOssBucket');
        $this->accessPath = '';
        $this->maxSize = systemConfig()->get('Cloud.defaultDiskMaxSize');
    }

    /**
     * @return string
     */
    public function getAccessPath(): string
    {
        return $this->accessPath;
    }

    /**
     * @param string $access_path
     */
    public function setAccessPath(string $access_path): void
    {
        $this->accessPath = $access_path;
    }


    /**
     * @return int
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * @param int $maxSize
     */
    public function setMaxSize(int $maxSize): void
    {
        $this->maxSize = $maxSize;
    }


    /**
     * @return string
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * @param string $bucket
     */
    public function setBucket(string $bucket): void
    {
        $this->bucket = $bucket;
    }


    /**
     * @return string
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * @param string $vendor
     */
    public function setVendor(string $vendor): void
    {
        $this->vendor = $vendor;
    }

    /**
     * @return string
     */
    public function getAccessKeyId(): string
    {
        return $this->accessKeyId;
    }

    /**
     * @param string $accessKeyId
     */
    public function setAccessKeyId(string $accessKeyId): void
    {
        $this->accessKeyId = $accessKeyId;
    }

    /**
     * @return string
     */
    public function getAccessKeySecret(): string
    {
        return $this->accessKeySecret;
    }

    /**
     * @param string $accessKeySecret
     */
    public function setAccessKeySecret(string $accessKeySecret): void
    {
        $this->accessKeySecret = $accessKeySecret;
    }

    /**
     * @return string
     */
    public function getNode(): string
    {
        return $this->node;
    }

    /**
     * @param string $node
     */
    public function setNode(string $node): void
    {
        $this->node = $node;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

    /**
     *
     * @date : 2022/4/24 18:24
     * @return array
     * @author : 孤鸿渺影
     */
    #[ArrayShape(['vendor' => "mixed|string", 'is_default' => "bool|mixed", 'access_key_id' => "mixed|string", 'access_key_secret' => "mixed|string", 'max_size' => "int|mixed", 'node' => "mixed|string", 'bucket' => "mixed|string", 'base_path' => "mixed|string", 'access_path' => "mixed"])] public function toArray(): array
    {
        return [
            'vendor' => $this->vendor,
            'is_default' => $this->default,
            'access_key_id' => $this->accessKeyId,
            'access_key_secret' => $this->accessKeySecret,
            'max_size' => $this->maxSize,
            'node' => $this->node,
            'bucket' => $this->bucket,
            'base_path' => $this->basePath,
            'access_path' => $this->accessPath,
        ];
    }

    /**
     *
     * @date : 2022/4/24 18:26
     * @return string
     * @author : 孤鸿渺影
     */
    public function __toString(): string
    {
        // TODO: Implement __toString() method.
        return json_encode($this->toArray());
    }

}
