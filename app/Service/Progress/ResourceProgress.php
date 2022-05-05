<?php

namespace App\Service\Progress;

use Illuminate\Support\Facades\Cache;
use JetBrains\PhpStorm\ArrayShape;

/**
 * 孤鸿渺影
 * 2022/5/4 0:49
 * DeleteResourceProgress
 */
class ResourceProgress implements ProgressInterface
{

    private string $key;

    private int $total = 0;

    private int $completeTotal = 0;

    private string $currentResource = '';

    private int $totalSize = 0;

    private int $completeSize = 0;

    private ?string $status;

    /**
     * @param string $key
     */
    public function __construct(string $key = '')
    {
        if (empty($key)) {
            $this->key = getUuid();
        } else {
            $this->key = $key;
        }
        $this->status = '等待';
    }


    /**
     * 自增
     * @date : 2022/5/4 12:18
     * @param $size
     * @param $currentResource
     * @author : 孤鸿渺影
     */
    public function increment($size, $currentResource)
    {
        $this->completeSize += $size ?? 0;
        $this->completeTotal++;
        $this->currentResource = $currentResource;

    }

    #[ArrayShape(['total' => "int", 'complete_total' => "int", 'total_size' => "int", 'complete_size' => "int", 'current_resource' => "null|string", 'status' => "string", 'time' => "int"])] public function toArray()
    {
        return [
            'total' => $this->total ?? 0,
            'complete_total' => $this->completeTotal ?? 0,
            'total_size' => $this->totalSize ?? 0,
            'complete_size' => $this->completeSize ?? 0,
            'current_resource' => $this->currentResource ?? null,
            'status' => $this->status,
            'time' => time()
        ];
    }
    /**
     *
     * @date : 2022/5/4 13:21
     * @return bool|string
     * @author : 孤鸿渺影
     */
    public function toJson(): bool|string
    {
        return json_encode($this->toArray());
    }

    /**
     *
     * @date : 2022/5/4 13:21
     * @author : 孤鸿渺影
     */
    public function save()
    {
        Cache::put('progress:resource_' . $this->key, $this->toJson());
    }

    /**
     *
     * @date : 2022/5/4 18:01
     * @author : 孤鸿渺影
     */
    public function remove()
    {
        Cache::forget('progress:resource_' . $this->key);
    }

    /**
     * @return mixed
     */
    public function getKey(): mixed
    {
        return $this->key;
    }

    /**
     * @param mixed|string $key
     */
    public function setKey(mixed $key): void
    {
        $this->key = $key;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * @return int
     */
    public function getCompleteTotal(): int
    {
        return $this->completeTotal;
    }

    /**
     * @param int $completeTotal
     */
    public function setCompleteTotal(int $completeTotal): void
    {
        $this->completeTotal = $completeTotal;
    }

    /**
     * @return string
     */
    public function getCurrentResource(): string
    {
        return $this->currentResource;
    }

    /**
     * @param string $currentResource
     */
    public function setCurrentResource(string $currentResource): void
    {
        $this->currentResource = $currentResource;
    }

    /**
     * @return int
     */
    public function getTotalSize(): int
    {
        return $this->totalSize;
    }

    /**
     * @param int $totalSize
     */
    public function setTotalSize(int $totalSize): void
    {
        $this->totalSize = $totalSize;
    }

    /**
     * @return int
     */
    public function getCompleteSize(): int
    {
        return $this->completeSize;
    }

    /**
     * @param int $completeSize
     */
    public function setCompleteSize(int $completeSize): void
    {
        $this->completeSize = $completeSize;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }


}
