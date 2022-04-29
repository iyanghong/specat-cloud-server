<?php


namespace App\Core\Constructors\Model;


interface BatchModelInterface
{

    /**
     * 批量修改
     * @param $data
     * @return bool|int|void
     */
    public function update(array $data);
}