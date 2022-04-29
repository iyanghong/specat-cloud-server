<?php


namespace App\Service\PushIncluded;


class BaiduPush implements ChannelDriveInterface
{

    private $token = 'HtL8KaNWHcPdGj4c';
    private $site = 'www.yhong.info';
    public function handle(array $links = []){
        $api = "http://data.zz.baidu.com/urls?site={$this->site}&token={$this->token}";
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $links),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        return $result;
    }

}