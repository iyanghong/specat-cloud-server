<?php


namespace App\Core\Utils\MaskCrypt;


interface MaskCryptInterface
{
    /**
     * 加密
     * @param string $encryptStr
     * @return string
     */
    public function encrypt(string $encryptStr): string;

    /**
     * 解密
     * @param string $encryptStr
     * @return string
     */
    public function decrypt(string $encryptStr): string;
}