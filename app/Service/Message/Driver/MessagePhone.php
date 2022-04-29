<?php


namespace App\Service\Message\Driver;


use App\Service\Message\MessageInterface;

class MessagePhone implements MessageInterface
{
    public function errorPwd($user, $content = '')
    {
        // TODO: Implement errorPwd() method.
    }
    public function sendRegisterVerifyCode(string $address = '')
    {
        // TODO: Implement sendRegisterVerifyCode() method.
    }
    public function verifyCode(string $address = '', string $name = '')
    {
        // TODO: Implement verifyCode() method.
    }
    /**
     * 获取验证码
     * @param string $address
     * @date : 2021/5/12 20:47
     * @author : 孤鸿渺影
     * @return mixed
     * @throws MessageException
     */
    public function getCode(string $address = ''){}
}