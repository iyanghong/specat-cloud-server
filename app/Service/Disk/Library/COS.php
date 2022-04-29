<?php


namespace App\Service\Disk\Library;

use Qcloud\Cos\Client as CosClient;
class COS implements DiskClient
{
    //密钥 SecretId
    private string $AccessSecretId;
    //密钥 SecretKey
    private string $AccessSecretKey;
    //存储桶地域
    private string $region;

    private $client;

    public function __construct($secretId,$secretKey,$region)
    {
        $this->region = $region;
        $this->client = new CosClient([
            'region' => $region,
            'credentials' => [
                'secretId' => $secretId,
                'secretKey' => $secretKey,
            ]
        ]);
    }

    public function upload(string $bucket, string $path, string $file, $option)
    {
        if(!file_exists($file)){
            throw new \Exception($file . " file does not exist");
        }
        $file = fopen($file, 'rb');
        return $this->client->Upload($bucket,$path,$file);
    }

    /**
     * @param string $bucket 存储桶
     * @param string $path 上传存储路径
     * @param string $file  文件原始路径
     * @param $option 参数
     * @return mixed
     * @throws \Exception
     */
    public function putObject(string $bucket,string $path,string $content,$option = [])
    {
        $uploadData = [
            'Bucket' => $bucket,
            'Key' => $path,
            'Body' => $content
        ];
        if(!empty($option['ContentType'])){
            $uploadData['ContentType'] = $option['ContentType'];
        }
        return $this->client->putObject($uploadData);
    }

    /**
     * 复制对象
     * @param string $bucket 存储桶
     * @param string $from 路径
     * @param string $to 新路径
     * @return mixed
     */
    public function copyObject(string $bucket,string $from,string $to){
        return $this->client->copyObject([
            'Bucket' => $bucket,
            'Key' => $from,
            'CopySource' => $bucket .'/' . $this->region . $to
        ]);
    }

    /**
     * 移动对象
     * @param string $bucket 存储桶
     * @param string $from 路径
     * @param string $to 新路径
     * @return mixed
     */
    public function moveObject(string $bucket,string $from,string $to)
    {
        $this->client->copyObject([
            'Bucket' => $bucket,
            'Key' => $from,
            'CopySource' => $bucket .'/' . $this->region . $to
        ]);
        return $this->deleteObject($bucket,$from);
    }


    /**
     * 删除单个对象
     * @param string $bucket 存储桶
     * @param string $path 路径
     * @return mixed
     */
    public function deleteObject(string $bucket,string $path){
        return $this->client->deleteObject([
            'Bucket' => $bucket,
            'Key' => $path
        ]);
    }

    /**
     * 删除多个对象
     * @param string $bucket 存储桶
     * @param array $path 路径集合
     * @return mixed
     */
    public function deleteObjects(string $bucket,array $pathList){
        return $this->client->deleteObjects([
            'Bucket' => $bucket,
            'Objects' => $pathList
        ]);
    }



}