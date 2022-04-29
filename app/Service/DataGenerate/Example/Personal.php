<?php


namespace App\Service\DataGenerate\Example;


use App\Service\DataGenerate\DataGenerate;

class Personal
{
    private string $name;
    private string $sex;
    private string $email;
    private string $phone;
    private string $nickname;
    private string $info;
    private string $nation;
    private string $carId;
    private string $birthday;
    private int $age;
    private string $animals;
    private string $constellation;
    private string $ip;
    private int $addressCode;
    private string $addressDetail;
    private string $uuid;


    public function __construct()
    {
        $generate = new DataGenerate();
        $this->name = $generate->name();
        $sex = ['男', '女'];
        $this->sex = $sex[array_rand($sex, 1)];
        $this->email = $generate->email();
        $this->phone = $generate->phone();
        $this->nickname = $generate->nickName(true);
        $this->info = $generate->info();
        $this->nation = $generate->nation();
        $this->carId = $generate->idCard();
        $this->birthday = date('Y-m-d', strtotime(substr($this->carId, 6, 8)));
        $constellation = $this->get_constellation($this->birthday);
        $this->age = $constellation['age'];
        $this->animals = $constellation['animals'];
        $this->constellation = $constellation['constellation'];
        $this->ip = $generate->ip();
        $this->addressCode = $generate->addressCode();
        $this->addressDetail = $generate->home();
        $this->uuid = $generate->uuid();
    }

    /**
     * 根据生日获取星座、年龄、生肖
     * @param $mydate
     * @param string $symbol
     * @return mixed
     * @Author : TS
     * @Date : 2019/12/22 21:56
     */
    private function get_constellation($mydate, $symbol = '-')
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

    public function toArray()
    {
        // 实现的抽象类方法，指定需要被序列化JSON的数据
        $data = [];
        foreach ($this as $key => $val) {
            if ($val !== null) $data[$key] = $val;
        }
        return $data;
    }
    public function __toString()
    {
        // TODO: Implement __toString() method.
        return json_encode($this->toArray());
    }

    /**
     * @return mixed|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return mixed|string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return mixed|string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @return mixed|string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return mixed|string
     */
    public function getNation()
    {
        return $this->nation;
    }

    /**
     * @return string
     */
    public function getCarId(): string
    {
        return $this->carId;
    }

    /**
     * @return false|string
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @return mixed
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @return mixed
     */
    public function getAnimals()
    {
        return $this->animals;
    }

    /**
     * @return mixed
     */
    public function getConstellation()
    {
        return $this->constellation;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getAddressCode(): int
    {
        return $this->addressCode;
    }

    /**
     * @return string
     */
    public function getAddressDetail(): string
    {
        return $this->addressDetail;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }
}