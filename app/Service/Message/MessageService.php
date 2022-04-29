<?php


namespace App\Service\Message;


use App\Service\Message\Driver\MessageEmail;
use App\Service\Message\Driver\MessagePhone;

class MessageService
{
    /**
     *
     * Date : 2021/4/20 19:58
     * Author : 孤鸿渺影
     * @return MessageInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function email() :MessageInterface
    {
        $messageEmail = new MessageEmail();
        $messageEmail->setSiteName(systemConfig()->get('Sys.SystemName','孤鸿渺影'));
        return $messageEmail;
    }

    public static function phone() :MessageInterface
    {
        $messagePhone = new MessagePhone();
        return $messagePhone;
    }
}