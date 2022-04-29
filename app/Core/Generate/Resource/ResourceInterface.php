<?php


namespace App\Core\Generate\Resource;


use App\Core\Generate\Table\Table;

interface ResourceInterface
{

    /**
     * @return mixed
     */
    public function handle();
    /**
     * 获取资源内容
     * @return string
     */
    public function getFileContent(): string;

    /**
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * @return string
     */
    public function getMessage(): string;


    /**
     * @return bool
     */
    public function isForce(): bool;
    /**
     * @param bool $force
     * @return ResourceInterface
     */
    public function setForce(bool $force): ResourceInterface;
    /**
     * @return Config
     */
    public function getConfig(): Config;
    /**
     * @param Config $config
     */
    public function setConfig(Config $config): void;
    /**
     * @return ResourceFile
     */
    public function getResourceFile(): ResourceFile;

    /**
     * @param ResourceFile $resourceFile
     * @return ResourceInterface
     */
    public function setResourceFile(ResourceFile $resourceFile): ResourceInterface;
}