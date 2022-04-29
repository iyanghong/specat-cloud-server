<?php


namespace App\Core\Utils\MaskCrypt;


class MaskCryptService implements MaskCryptInterface
{
    private $iv = "0102030405060708";//密钥偏移量IV，可自定义

    private $encryptKey = "";//AESkey，可自定义

    private $method = 'DES-ECB';//加密方法
    private $options = 0;//数据格式选项（可选）


    //加密
    public function encrypt(string $encryptStr): string
    {
        $encrypted  = openssl_encrypt($encryptStr, $this->method, $this->encryptKey, $this->options);
        return base64_encode($encrypted);
    }

    //解密
    public function decrypt(string $encryptStr): string
    {
        $encryptedData = base64_decode($encryptStr);

        $encryptedData = openssl_decrypt($encryptedData, $this->method, $this->encryptKey, $this->options);
        return $encryptedData;
    }

    /**
     * @param string $iv
     */
    public function setIv(string $iv): void
    {
        $this->iv = $iv;
    }

    /**
     * @param string $encryptKey
     */
    public function setEncryptKey(string $encryptKey): void
    {
        $this->encryptKey = $encryptKey;
    }
}