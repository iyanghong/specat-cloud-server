<?php


namespace App\Core\DesignMode;


trait Singleton
{
    private static $instance = null;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct()
    {

    }

    private function __clone()
    {

    }
}