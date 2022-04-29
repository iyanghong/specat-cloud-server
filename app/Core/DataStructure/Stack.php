<?php

namespace App\Core\DataStructure;

class Stack
{
    private int $size;
    private int $top = -1;
    private array $stack;

    public function __construct($size = 1024)
    {
        $this->size = $size;
        $this->stack = array();
    }

    /**
     * 判断栈是否为空
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->top == -1;
    }

    /**
     * 判断栈是否已满
     * @return bool
     */
    public function isFull(): bool
    {
        return !($this->top < $this->size - 1);
    }

    /**
     * 入栈
     * @param $data
     * @return bool
     */
    public function push($data): bool
    {
        if ($this->isFull()) {
            return false;
        }
        $this->stack[] = $data;
        $this->top++;
        return true;
    }

    /**
     * 出栈
     * @return bool
     */
    public function pop(): bool
    {
        if ($this->isEmpty()) {
            return false;
        }
        unset($this->stack[$this->top]);
        $this->top--;
        return true;
    }
}
