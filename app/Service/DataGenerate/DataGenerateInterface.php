<?php


namespace App\Service\DataGenerate;


interface DataGenerateInterface
{
    /**
     * @Notes: 获取UUID
     * @Interface uuid
     * @param string $format 分隔符
     * @return string
     * @Author: TS
     * @Time: 2020-06-16   20:19
     */
    public function uuid(string $format = ''): string;

    /**
     * @Notes:模拟生成中文
     * @Interface china
     * @param int $length 生成汉字数量
     * @return string
     * @Author: TS
     * @Time: 2020-06-16   20:48
     */
    public function chinese(int $length = 1): string;

    /**
     * @Notes: 模拟生成字符串
     * @Interface char
     * @param int $length 长度 默认16位
     * @return bool|string
     * @Author: TS
     * @Time: 2020-06-16   20:48
     */
    public function char(int $length = 16): string;

    /**
     * @Notes: 模拟生成姓名
     * @Interface name
     * @param string $length 名 长度
     * @return mixed|string
     * @Author: TS
     * @Time: 2020-06-16   20:47
     */
    public function name(int $length = -1): string;


    /**
     * @Notes:模拟生成昵称
     * @Interface nickName
     * @param bool $enhance 数据增强
     * @return mixed
     * @Author: TS
     * @Time: 2020-06-18   14:14
     */
    public function nickName(bool $enhance = false): string;

    /**
     * @Notes: 模拟生成个性签名
     * @Interface info
     * @return mixed
     * @Author: TS
     * @Time: 2020-06-16   20:52
     */
    public function info(): string;

    /**
     * @Notes:模拟生成邮箱
     * @Interface email
     * @param string $length
     * @return string
     * @Author: TS
     * @Time: 2020-06-16   20:57
     */
    public function email(int $length = -1): string;

    /**
     * @Notes: 模拟生成手机号
     * @Interface phone
     * @return mixed|string
     * @Author: TS
     * @Time: 2020-06-16   20:59
     */
    public function phone(): string;

    /**
     * @Notes: 模拟生成民族
     * @Interface nation
     * @return mixed
     * @Author: TS
     * @Time: 2020-06-16   21:00
     */
    public function nation(): string;

    /**
     * @Notes: 模拟生成地址
     * @Interface home
     * @return string
     * @Author: TS
     * @Time: 2020-06-16   21:14
     */
    public function home(): string;

    public function addressCode(): int;

    /**
     * @Notes:模拟生成身份证号码
     * @Interface idCard
     * @param int $minAge 最小年龄
     * @param int $maxAge 最大年龄
     * @return string
     * @Author: TS
     * @Time: 2020-06-16   21:28
     */
    public function idCard(int $minAge = 6, int $maxAge = 60): string;

    /**
     * @Notes: 模拟生成国内IP
     * @Interface ip
     * @return string
     * @Author: TS
     * @Time: 2020-06-16   21:42
     */
    public function ip(): string;

    /**
     * @Notes: 模拟生成出生日期
     * @Interface birthday
     * @param int $min
     * @param int $max
     * @return false|string
     * @Author: TS
     * @Time: 2020-06-18   14:00
     */
    public function birthday(int $min = 6, int $max = 60): string;
}