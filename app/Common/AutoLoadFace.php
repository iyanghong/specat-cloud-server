<?php




function resourceConstructor(\App\Core\Constructors\Model\BaseModel $model, string $cacheKeySuffix = ''): \App\Core\Constructors\Controller\BaseResourceControllerInterface
{
    return new \App\Core\Constructors\Controller\BaseResourceController($model, $cacheKeySuffix);
}


function localDisk($baseLocal = 'local'): \App\Service\Disk\LocalDiskInterface
{
    return new \App\Service\Disk\LocalDisk($baseLocal);
}

/**
 * Date : 2021/4/19 21:36
 * Author : 孤鸿渺影
 * @return \App\Service\Disk\CloudDiskInterface
 * @throws \Psr\SimpleCache\InvalidArgumentException
 */
function cloudDisk() :\App\Service\Disk\CloudDiskInterface
{
    return \App\Service\Disk\OssDiskService::handle();
}

/**
 *
 * Date : 2021/4/20 19:59
 * Author : 孤鸿渺影
 * @return \App\Service\Message\MessageInterface
 * @throws \Psr\SimpleCache\InvalidArgumentException
 */
function messageMail():\App\Service\Message\MessageInterface
{
    return \App\Service\Message\MessageService::email();
}
function maskCrypt(): \App\Core\Utils\MaskCrypt\MaskCryptInterface
{
    $crypt = new \App\Core\Utils\MaskCrypt\MaskCryptService();
    $crypt->setEncryptKey(env('CRYPT_KEY','92644664'));
    return $crypt;
}

/**
 *
 * @param \App\Core\Constructors\Model\BaseModel $model
 * @param array $option
 * Date : 2021/4/21 20:22
 * Author : 孤鸿渺影
 * @return \App\Core\Constructors\Controller\FilterConstructor
 */
function filterConstructor(\App\Core\Constructors\Model\BaseModel $model,array $option = [])
{
    return new \App\Core\Constructors\Controller\FilterConstructor($model,null,$option);
}
/**
 * 系统配置
 * @Interface SystemConfig
 * @return \App\Service\SystemConfig\SystemConfigServiceInterface
 * @Author: TS
 * @Time: 2020-06-22   21:34
 */
function systemConfig():\App\Service\SystemConfig\SystemConfigServiceInterface
{
    return app('SystemConfig');
}

/**
 * @Notes:当前用户
 * @Interface OnlineMember
 * @return \App\Service\OnlineMember\OnlineMemberInterface
 * @Author: TS
 * @Time: 2020-06-22   21:36
 */
function onlineMember(): \App\Service\OnlineMember\OnlineMemberInterface
{
    return app('OnlineMember');
}

/**
 * 设备信息驱动
 * Date : 2021/4/22 20:52
 * Author : 孤鸿渺影
 * @return \Jenssegers\Agent\Agent
 */
function agent() : \Jenssegers\Agent\Agent
{
    return app('agent');
}


/**
 * 数据模拟类
 * @Interface generate
 * @return \App\Service\DataGenerate\DataGenerateInterface
 * @Author: TS
 * @Time: 2020-06-23   19:30
 */
function generate(): \App\Service\DataGenerate\DataGenerateInterface
{
    return new \App\Service\DataGenerate\DataGenerate();
}
