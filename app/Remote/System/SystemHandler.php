<?php


namespace App\Remote\System;


use App\Core\Swoole\Handle\SwooleHandler;

class SystemHandler
{
    public function getTest(){
        return 'hello swoole rpc';
    }
    public function getUser(){
        $user = \DB::query('select * from users where user_id = 10000')->get();
        return json_encode($user);
    }

}