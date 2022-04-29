<?php


namespace App\Core\Constructors\Controller;


interface DataHandlerInterface
{

    /**
     * 填充数据
     * @param $mode --模式
     * @return false|int|string|null
     */
    public function fill($mode);

    /**
     * 处理数据
     * @param $value
     * @param $mode
     * @return false|int|string
     */
    public function processing($value, $mode);
}