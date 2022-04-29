<?php


namespace App\Service\SystemConfig;


use App\Models\System\SystemConfig as SystemConfigModel;
use Illuminate\Support\Facades\Cache;

class SystemConfigService implements SystemConfigServiceInterface
{
    private string $key = 'System';//当前系统编号

    public function __construct(?string $key)
    {
        if ($key) $this->key = $key;
    }

    /**
     * @param string $key
     * @param null $default
     * Date : 2021/4/19 21:26
     * Author : 孤鸿渺影
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(string $key, $default = null)
    {
        // TODO: Implement get() method.
        foreach ($this->getByRedis() as $item => $value) {
            if ($key === $item) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * @param string $key
     * @param array|null $default
     * Date : 2021/4/19 21:29
     * Author : 孤鸿渺影
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getArray(string $key, array $default = []): ?array
    {
        foreach ($this->getByRedis() as $item => $value) {
            if ($key === $item) {
                if(is_array($value)){
                    return $value;
                }
                if ($data = json_decode($value, true)) {
                    return $data;
                }
                break;
            }
        }
        return $default;
    }

    /**
     * @return array|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getList(): ?array
    {
        // TODO: Implement getList() method.
        return $this->getByRedis();
    }

    /**
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function getByRedis(): array
    {
        $key = 'SystemConfig:' . $this->key . '';
        $data = json_decode(Cache::get($key), true);
        if ($data == null) {
            $data = $this->refresh();
        }
        if (empty($data)) {
            return [];
        }
        return $data;
    }

    /**
     * 刷新系统配置缓存
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function refresh(): array
    {
        $key = 'SystemConfig:' . $this->key . '';
        $configList = SystemConfigModel::all();
        $config = [];
        foreach ($configList as $item) {
            if ($item['type'] == 5) {
                $config[$item['code']] = maskCrypt()->decrypt($item['value']);
            } else {
                $config[$item['code']] = $item['value'];
            }

        }
        Cache::set($key, json_encode($config));
        return $config;
    }


    /**
     * @param mixed $key
     */
    public function setKey($key): void
    {
        $this->key = $key;
    }
}