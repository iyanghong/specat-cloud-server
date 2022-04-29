<?php


namespace App\Service\Faker;

use Faker\Generator;
use Faker\Provider\Base as FakerBase;

class Faker
{

    private $exceptGeneratorMethod = [
        'regexify',//根据正则表达式返回字串
    ];
    private $exceptGeneratorProperty = [
        'regexify',//根据正则表达式返回字串
    ];

    public function handle()
    {

    }

    public function get($generateKey = [])
    {
        $faker = app(\Faker\Generator::class);
        $data = [];
        foreach ($generateKey as $value) {
            if (in_array($value, $this->exceptGeneratorMethod)) {
                $data[$value] = $faker->{$value}();
            } elseif (in_array($value, $this->exceptGeneratorProperty)) {
                $data[$value] = $faker[$value];
            }
        }
        return $data;
    }

    public function getAddress()
    {
//        $faker = app(\Faker\Generator::class);
        $generator = new \Faker\Generator();
//        $faker = new \Faker\Provider\Address($generator);
        /* @var $faker \Faker\Provider\Address */
        $faker = \Faker\Factory::create('zh_CN');

        $data = [
            'city' => $faker->city(),
            'state' => $faker->state(),
            'stateAbbr' => $faker->stateAbbr(),
            'citySuffix' => $faker->citySuffix(),
            'streetSuffix' => $faker->streetSuffix(),
            'buildingNumber' => $faker->buildingNumber(),
            'streetName' => $faker->streetName(),
            'streetAddress' => $faker->streetAddress(),
            'postcode' => $faker->postcode(),
            'address' => $faker->address(),
            'latitude' => $faker->latitude(),
            'longitude' => $faker->longitude(),
//            'secondaryAddress' => $faker->secondaryAddress()
        ];
        return $data;
//        $data['cityPrefix'] = $faker->cityPrefix;//城市前缀.如：Lake
        $data['secondaryAddress'] = $faker->secondaryAddress;//二级地址.如：Suite 061
        $data['state'] = $faker->state;//州、省（如：Colorado、四川省）
        $data['stateAbbr'] = $faker->stateAbbr;//省份简称.如：晋、蒙、浙、冀
        $data['citySuffix'] = $faker->citySuffix;//城市后缀.如：side、land、port、Ville
        $data['streetSuffix'] = $faker->streetSuffix;//街道后缀.如：Ramp、Plains
        $data['buildingNumber'] = $faker->buildingNumber;//建筑物编号
        $data['city'] = $faker->city;//城市
        $data['streetName'] = $faker->streetName;//街道名称
        $data['streetAddress'] = $faker->streetAddress;//街道地址
        $data['postcode'] = $faker->postcode;//邮政编码
        $data['address'] = $faker->address;//地址（城市+区）
        $data['country'] = $faker->country;//国家
        $data['latitude'] = $faker->latitude;//纬度 latitude($min = -90, $max = 90)
        $data['longitude'] = $faker->longitude;//经度 longitude($min = -180, $max = 180)
        return $data;
    }
}