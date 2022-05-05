<?php


namespace App\Core\Utils;


class CurlHttp
{
    private array $options = array(
        /**
         * 请求类型
         */
        'method' => 'GET',
        /**
         * 请求头
         */
        'headers' => array(),
        /**
         * 请求参数、这里会放在body里进行发送，一般用于POST、PATCH等请求
         */
        'data' => array(),
        /**
         * 请求参数、这里会放在url后面拼接，GET请求需要放在此处
         */
        'params' => array(),
        /**
         * 响应数据类型。text|json
         */
        'responseType' => 'text',
        /**
         * 请求超时时间(单位为毫秒)
         */
        'timeout' => 3000,
        /**
         * 请求成功回调函数
         */
        'success' => null,
        /**
         * 请求失败回调函数
         */
        'error' => null,
        /**
         * 请求完成（无论成功与失败）回调函数
         */
        'finally' => null
    );

    /**
     * GET请求
     * @param $url
     * @param array $params
     * @param \Closure|null $closure
     * @date : 2021/7/16 19:19
     * @return bool|string
     * @throws \Exception
     * @author : 孤鸿渺影
     */
    public function get($url, array $params = [], \Closure $closure = null)
    {
        $options = [
            'url' => $url,
            'method' => 'GET',
            'success' => $closure,
            'params' => $params
        ];
        return $this->http($options);
    }

    /**
     * POST请求
     * @param $url
     * @param array $data
     * @param \Closure|null $closure
     * @date : 2021/7/16 19:26
     * @return bool|string
     * @throws \Exception
     * @author : 孤鸿渺影
     */
    public function post($url, array $data = [], \Closure $closure = null)
    {
        $options = [
            'url' => $url,
            'method' => 'POST',
            'success' => $closure,
            'data' => $data
        ];
        return $this->http($options);
    }

    public function http(array $options = [])
    {
        if (empty($options['url'])) throw new \Exception('url不能为空');
        $url = $options['url'];
        $method = $options['method'] ?? $this->options['method'];
        $headers = (isset($options['headers']) && is_array($options['headers'])) ? $options['headers'] : $this->options['headers'];
        $data = (isset($options['data']) && is_array($options['data'])) ? $options['data'] : $this->options['data'];
        $params = (isset($options['params']) && is_array($options['params'])) ? $options['params'] : $this->options['params'];
        $success = (isset($options['success']) && $this->is_function($options['success'])) ? $options['success'] : $this->options['success'];
        $error = (isset($options['error']) && $this->is_function($options['error'])) ? $options['error'] : $this->options['error'];
        $finally = (isset($options['finally']) && $this->is_function($options['finally'])) ? $options['finally'] : $this->options['finally'];
        $responseType = (isset($options['responseType']) && in_array($options['responseType'], ['text', 'json'])) ? $options['responseType'] : $this->options['responseType'];

        $curl = curl_init();
        //请求类型
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, (isset($options['timeout']) && is_numeric($options['timeout'])) ? $options['timeout'] : $this->options['timeout']);
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if (is_array($options['headers'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $options['headers']);
            /*if (isset($options['headers']['cookie'])) {
                curl_setopt($curl, CURLOPT_COOKIE, $options['headers']['cookie']);
            }*/
        }
        // 解析url参数
        $paramStr = '';
        foreach ($params as $key => $param) {
            $paramStr !== '' && ($paramStr .= "&");
            $paramStr .= $key . "=" . urlencode($param);
        }
        if ($paramStr) {
            $url .= '?' . $paramStr;
        }
        // 请求地址
        curl_setopt($curl, CURLOPT_URL, $url);
        if (1 == strpos("$" . $url, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $out_put = curl_exec($curl);
        if (curl_error($curl)) {
            if ($error) {
                $error($curl);
            }
        } else {
            if ($responseType == 'json') {
                $out_put = json_decode($out_put, true);
            }
            if ($success) {
                $success($out_put);
            }
        }
        if ($finally) {
            $finally($out_put);
        }

        curl_close($curl);
        return $out_put;
    }


    /**
     * 判断是否是方法
     * @param $f
     * @date : 2021/7/16 19:50
     * @return bool
     * @author : 孤鸿渺影
     */
    private function is_function($f)
    {
        return (is_string($f) && function_exists($f)) || (is_object($f) && ($f instanceof \Closure));
    }
}
