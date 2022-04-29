<?php


namespace App\Service\Message;


use App\Service\Message\Driver\MessageException;

interface MessageInterface
{
    public function verifyCode(string $address = '',string $name = '');

    public function errorPwd($user,$content = '');

    /**
     * @Notes:注册账户邮箱验证码
     * @Interface sendRegisterVerifyCode
     * @param string $address
     * @return array
     * @Author: TS
     * @Time: 2020-12-04   13:14
     */
    public function sendRegisterVerifyCode(string $address = '');

    /**
     * 获取验证码
     * @param string $address
     * @date : 2021/5/12 20:47
     * @author : 孤鸿渺影
     * @return mixed
     * @throws MessageException
     */
    public function getCode(string $address = '');
}