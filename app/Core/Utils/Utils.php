<?php


namespace App\Core\Utils;


class Utils
{

    /**
     * 根据生日获取星座、年龄、生肖
     * @param $mydate
     * @param string $symbol
     * @return mixed
     * @Author : TS
     * @Date : 2019/12/22 21:56
     */
    public function get_constellation($mydate, $symbol = '-')
    {

        //计算年龄
        $birth = $mydate;
        list($by, $bm, $bd) = explode($symbol, $birth);
        $cm = date('n');
        $cd = date('j');
        $age = date('Y') - $by - 1;
        if ($cm > $bm || $cm == $bm && $cd > $bd) $age++;
        $array['age'] = $age;
        //计算生肖
        $animals = array(
            '鼠', '牛', '虎', '兔', '龙', '蛇',
            '马', '羊', '猴', '鸡', '狗', '猪'
        );
        $key = ($by - 1900) % 12;
        $array['animals'] = $animals[$key];

        //计算星座
        $constellation_name = array(
            '水瓶座', '双鱼座', '白羊座', '金牛座', '双子座', '巨蟹座',
            '狮子座', '处女座', '天秤座', '天蝎座)', '射手座', '摩羯座'
        );
        if ($bd <= 22) {
            if ('01' !== $bm) $constellation = $constellation_name[$bm - 2]; else $constellation = $constellation_name[11];
        } else $constellation = $constellation_name[$bm - 1];
        $array['constellation'] = $constellation;

        return $array;
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
    public function replaceImgSrc($content = '', $img_list = [], $id = '', $edit = false)
    {
        $img_list = json_decode($img_list, true);
        foreach ($img_list as $key => $item) {
            if ($edit) {
                if (strpos($item, 'ache/images')) {
                    $src = 'users/' . getNowUserId() . '/article/' . $id . '/' . explode('cache/images/', $item)[1];
                    $img_list[$key] = $src;
                    $content = str_replace($item, $src, $content);
                }
            } else {
                $src = 'users/' . getNowUserId() . '/article/' . $id . '/' . explode('cache/images/', $item)[1];
                $img_list[$key] = $src;
                $content = str_replace($item, $src, $content);
            }

        }
        return [
            'content' => $content,
            'img_list' => $img_list
        ];
    }


    /**
     * @Notes:数组转对象
     * @Interface arrayTransitionObject
     * @param array $array
     * @return object
     * @Author: TS
     * @Time: 2020-07-06   23:58
     */
    public function arrayTransitionObject(array $array) :object
    {
        if (is_array($array)) {
            $obj = new class
            {
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
     * 规范化标识
     * @Interface formatterProjectCode
     * @param $key
     * @return bool|string
     * @Author: TS
     * @Time: 2020-08-08   19:05
     */
    function checkFormatterCode($key, $shielding = null)
    {
        if (empty($key)) {
            return '标识不能为空';
        }
        $key = strtolower($key);  //全部转成小写
        if (stripos($key, '.')) {
            return '不允许包含带.号';
        }
        if (stripos($key, '/')) {
            return '不允许包含/号';
        }
        if (stripos($key, '\\')) {
            return '不允许包含\号';
        }
        if ($shielding === null) {
            $shielding = ['admin', 'service', 'system', 'code', 'develop', 'user', 'users'];
        }
        if (in_array($key, $shielding)) {
            return '不允许使用 ' . $key . ' 作为标识';
        }
        return true;
    }
}