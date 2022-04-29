<?php


namespace App\Service\SystemConfig;


interface SystemConfigServiceInterface
{

    /**
     * @param string $key
     * @param null $default
     * Date : 2021/4/19 21:26
     * Author : 孤鸿渺影
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(string $key,$default = null);

    /**
     * @param string $key
     * @param array|null $default
     * Date : 2021/4/19 21:29
     * Author : 孤鸿渺影
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getArray(string $key, array $default = []): ?array;
    /**
     * @Notes:获取系统配置列表
     * @Interface getList
     * @return array|null
     * @Author: TS
     * @Time: 2020-06-24   11:54
     */
    public function getList() : ?array ;
    /**
     * @Notes:刷新系统配置缓存
     * @Interface refresh
     * @return array
     * @Author: TS
     * @Time: 2020-06-24   11:53
     */
    public function refresh(): array;
}