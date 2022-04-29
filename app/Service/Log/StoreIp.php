<?php


namespace App\Service\Log;


use App\Models\Log\IpDetail;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

trait StoreIp
{
    public function setIp($ip,Closure $closure = null)
    {
        if (empty($ip)) return false;
        $validate = Validator::make(['ip' => $ip], ['ip' => 'ipv4']);
        if ($validate->fails()) {
            return false;
        }
        /* @var $ipDetailModel Builder */
        $ipDetailModel = new IpDetail();
        $check = $ipDetailModel->where(['ip' => $ip])->count();
        if ($check > 0) {
            return false;
        }

        $data = $this->analyIp($ip);
        $data = toArray($data);
        if (!$data) return '';
        if ($data['message'] == 'success') {
            $data = $data['result'];

            $ipData = [
                'ip' => $ip,
                'ip_uuid' => getUuid(),
                'nation' => $data['nation'],
                'en_short' => $data['en_short'],
                'en_name' => $data['en_name'],
                'province' => $data['province'],
                'city' => $data['city'],
                'district' => $data['district'],
                'code' => $data['adcode'],
                'longitude' => $data['lng'],
                'latitude' => $data['lat']
            ];
            $flag = $ipDetailModel->create($ipData);
            if (!$flag) {
                return false;
            }
            if($closure !==null){
                $closure($ipData);
            }
        }

        return true;
    }

    private function analyIp($ip)
    {
        $host = "https://ips.market.alicloudapi.com";
        $path = "/iplocaltion";
        $method = "GET";
        $appcode = env('API_IP_CODE', '');
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "ip=" . $ip;
        $bodys = "";
        $url = $host . $path . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        //curl_setopt($curl, CURLOPT_HEADER, true);   如不输出json, 请打开这行代码，打印调试头部状态码。
        //状态码: 200 正常；400 URL无效；401 appCode错误； 403 次数用完； 500 API网管错误
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $out_put = curl_exec($curl);
        return $out_put;
    }
}