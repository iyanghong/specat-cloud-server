<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ErrorPwd extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     *
     * @param array $data
     * Date : 2021/4/20 19:26
     * Author : 孤鸿渺影
     * @return ErrorPwd
     */
    public function build($data = [])
    {
        $data['localUrl'] = '';
        return $this->view('Email.ErrorPwd')->with($data);
    }
}
