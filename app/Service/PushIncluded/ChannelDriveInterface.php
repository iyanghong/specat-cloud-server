<?php


namespace App\Service\PushIncluded;


interface ChannelDriveInterface
{

    public function handle(array $links = []);
}