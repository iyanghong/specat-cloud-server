<?php


namespace App\Core\Utils;


use App\Core\Enums\ErrorCode;

class ApiResponse
{
    /**
     * 是否执行成功
     * @var bool
     */
    private bool $success;
    /**
     * 执行状态码
     * @var int
     */
    private int $code;

    /**
     * 提示信息
     * @var string
     */
    private string $message;

    /**
     * 数据包
     * @var array
     */
    private array $data;

    /**
     * 错误文件：行号
     * dev环境使用
     * @var string
     */
    private string $line;



    //分页start
    /**
     * 总记录数
     * @var int
     */
    private int $total;

    /**
     * 获取当前页页码。
     * @var int
     */
    private int $currentPage;
    /**
     * 获取最后一页的页码
     * @var int
     */
    private int $lastPage;

    /**
     * 是否有多页。
     * @var bool
     */
    private bool $hasPages;
    /**
     * 是否有更多页。
     * @var bool
     */
    private bool $hasMorePages;

    /**
     * 当前页数据的数量
     * @var int
     */
    private int $count;


    public function toJson() : array
    {
        !isset($this->success) && $this->success = false;
        !isset($this->code) && $this->code = $this->success ? ErrorCode::$ENUM_SUCCESS : ErrorCode::$ENUM_UNKNOWN_ERROR;
        empty($this->message) && $this->message = ErrorCode::getMessage($this->code);
        $response = [];
        foreach ($this as $key => $value){
            if($value !== null){
                $response[$key] = $value;
            }
        }
        return $response;
    }

    public function toString() : string
    {
        return json_encode($this->toJson());
    }
    //分页end

    public function setPaginate(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator)
    {
        $this->success = true;
        $this->data = $paginator->items();
        $this->lastPage = $paginator->lastPage();
        $this->currentPage = $paginator->currentPage();
        $this->total = $paginator->total();
        $this->hasPages = $paginator->hasPages();
        $this->hasMorePages = $paginator->hasMorePages();
        $this->count = $paginator->count();
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
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

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getLine(): string
    {
        return $this->line;
    }

    /**
     * @param string $line
     */
    public function setLine(string $line): void
    {
        $this->line = $line;
    }





}