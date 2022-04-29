<?php


namespace App\Service\Disk\Library;


use OSS\Core\OssException;
use OSS\OssClient;

class OSS implements DiskClient
{
    //密钥 SecretId
    private string $AccessSecretId;
    //密钥 SecretKey
    private string $AccessSecretKey;
    //存储桶地域
    private string $region;

    private $client;

    private string $message;

    public function __construct($secretId,$secretKey,$region)
    {
        try{
            $this->client = new OssClient($secretId, $secretKey, $region);
        }catch (OssException $e){
            $this->message = $e->getMessage();
        }
    }

    /**
     * @throws OssException
     */
    public function upload(string $bucket, string $path, string $file, $option = [])
    {
        // TODO: Implement upload() method.
        return $this->client->uploadFile($bucket, $path, $file, $option);
    }

    /**
     * @throws OssException
     */
    public function copyObject(string $bucket, string $from, string $to)
    {
        // TODO: Implement copyObject() method.
        return $this->client->copyObject($bucket,$from,$bucket,$to);
    }
    public function deleteObject(string $bucket, string $path)
    {
        // TODO: Implement deleteObject() method.
        return $this->client->deleteObject($bucket,$path);

    }
    public function deleteObjects(string $bucket, array $pathList)
    {
        // TODO: Implement deleteObjects() method.
        return $this->client->deleteObjects($bucket,$pathList);
    }

    /**
     * @throws OssException
     */
    public function moveObject(string $bucket, string $from, string $to)
    {
        // TODO: Implement moveObject() method.
        $this->client->copyObject($bucket,$from,$bucket,$to);
        return $this->deleteObject($bucket,$from);
    }

    public function putObject(string $bucket, string $path, string $file, $option)
    {
        // TODO: Implement putObject() method.
        if(!file_exists($file)){
            throw new \Exception($file . " file does not exist");
        }
        $file = fopen($file, 'rb');
        if(!empty($option['ContentType'])){
            $uploadData['ContentType'] = $option['ContentType'];
        }
        return $this->client->putObject($uploadData);
    }
}
