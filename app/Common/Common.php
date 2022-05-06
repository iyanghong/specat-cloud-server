<?php
define('CACHE_TIME', 84600);


function getUserIdCode($userId)
{
    $arr = ['a', 'c', 'd', 'e', 'f', 'g'];
}

/**
 * 格式化单词
 * @param $value
 * @param string $format
 * @param string $addFormat
 * @return string
 */
function ucfirstAll($value, $format = '_', $addFormat = '')
{
    if (empty($value)) return '';
    $nameArr = explode($format, $value);
    $name = '';
    for ($i = 0; $i < sizeof($nameArr); $i++) {
        if ($i != 0) {
            $name .= $addFormat;
        }
        $name .= ucfirst($nameArr[$i]);
    }
    return $name;
}

function getResourcesPath(...$paths): string
{
    $path = '';
    foreach ($paths as $item) {
        $path = $path . '/' . trim($item, '/');
    }
    return trim($path, '/');
}

/**
 * 获取设备信息
 * @return string
 * @Author: TS
 * @Time: 2020-12-08   21:32
 */
function getRequestAgentOrigin(): string
{
    $device = agent()->device();
    $platform = agent()->platform();
    $platformVersion = agent()->version($platform);
    $browser = agent()->browser();
    $browserVersion = agent()->version($browser);
    $origin = "";
    $device && $origin .= "$device;";
    $platform && $origin .= "$platform $platformVersion;";
    $browser && $origin .= "$browser $browserVersion;";
    return $origin;
}


function toArray($object)
{
    if (is_null($object)) {
        return [];
    }
    if (is_string($object)) {
        $data = json_decode($object, true);
        if (is_array($data)) {
            return $data;
        } else {
            return [];
        }
    }
    return json_decode(json_encode($object), true);
}


/**
 * @Notes:获取当前url
 * @Interface curPageURL
 * @return string
 * @Author: TS
 * @Time: 2020-10-16   22:25
 */
function currentOrigin()
{
    $pageURL = request()->url();
    $uri = request()->getRequestUri();
    return str_replace($uri, '', $pageURL);
}


/**
 * @Notes: 获取用户Token
 * @Interface getUserToken
 * @param $id
 * @return string
 * @Author: TS
 * @Time: 2020-06-18   14:24
 */
function getUserToken($id)
{
    $id_arr = str_split($id);
    $t = time();
    $t_arr = str_split($t, strlen($t) / 2);
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $uuid = substr($charid, 0, 8) . $id_arr[0]
        . substr($charid, 8, 4) . $id_arr[1] . $t_arr[0]
        . substr($charid, 12, 4) . $id_arr[2]
        . substr($charid, 16, 4) . $id_arr[3] . $t_arr[1]
        . substr($charid, 20, 12) . $id_arr[4];
    return $uuid;
}


/**
 * @Describe : 获取uuid
 * @return string
 * @Author : TS
 * @Date : 2020/1/2 22:18
 */
function getUuid(): string
{
    return \Ramsey\Uuid\Uuid::uuid1()->getHex();
}


/**
 * 清除缓存
 * @Interface forgetCache
 * @param $keys
 * @Author: TS
 * @Time: 2020-12-11   14:37
 */
function forgetCache($keys)
{
    if (is_string($keys)) {
        \Illuminate\Support\Facades\Cache::forget($keys);
    } elseif (is_array($keys)) {
        foreach ($keys as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }
    }
}


/**
 * @Notes:数组转对象
 * @Interface arrayTransitionObject
 * @param array $array
 * @return object
 * @Author: TS
 * @Time: 2020-07-06   23:58
 */
function arrayTransitionObject(array $array)
{
    if (is_array($array)) {
        $obj = new class {
        };
        foreach ($array as $key => $val) {
            $obj->$key = $val;
        }
    } else {
        $obj = $array;
    }
    return $obj;
}

/**
 * @Notes: 替换图片路径
 * @Interface replaceImgSrc
 * @param string $content
 * @param array $img_list
 * @param string $id
 * @param bool $edit
 * @return array
 * @Author: TS
 * @Time: 2020-06-18   14:23
 */
function replaceImgSrc($content = '', $img_list = [], $id = '', $edit = false)
{
    $img_list = json_decode($img_list, true);
    foreach ($img_list as $key => $item) {
        if ($edit) {
            if (strpos($item, 'ache/images')) {
                $src = 'users/' . onlineMember()->getId() . '/article/' . $id . '/' . explode('cache/images/', $item)[1];
                $img_list[$key] = $src;
                $content = str_replace($item, $src, $content);
            }
        } else {
            if (strpos($item, 'ache/images')) {
                $src = 'users/' . onlineMember()->getId() . '/article/' . $id . '/' . explode('cache/images/', $item)[1];
                $img_list[$key] = $src;
                $content = str_replace($item, $src, $content);
            }
        }

    }
    return [
        'content' => $content,
        'img_list' => $img_list
    ];
}
