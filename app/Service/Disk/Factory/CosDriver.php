<?php

namespace App\Service\Disk\Factory;

use Illuminate\Http\UploadedFile;

/**
 * 孤鸿渺影
 * 2022/4/19 15:07
 * CosDriver
 */
class CosDriver implements DiskFactoryInterface
{

    public function upload(UploadedFile $file, string $diyPath = '', array $option = []): bool
    {
        // TODO: Implement upload() method.
    }

    public function copy(string $oldPath, string $newPath): bool
    {
        // TODO: Implement copy() method.
    }

    public function exist(string $path): bool
    {
        // TODO: Implement exist() method.
    }

    public function move(string $oldPath, string $newPath): bool
    {
        // TODO: Implement move() method.
    }

    public function delete(string $path): bool
    {
        // TODO: Implement delete() method.
    }

    public function getPath(): string
    {
        // TODO: Implement getPath() method.
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
    }
}
