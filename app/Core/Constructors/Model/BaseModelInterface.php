<?php


namespace App\Core\Constructors\Model;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface BaseModelInterface
{

    public function getFillable() : array ;

    /**
     * @Notes: 获取表字段（包括拼接table.）
     * @Interface getAllField
     * @return mixed
     * @Author: TS
     * @Time: 2020-06-16   9:40
     */
    public function getAllField() : array ;

    /**
     * @Describe : 获取表字段
     * @return mixed
     * @Author : TS
     * @Date : 2019/12/28 23:37
     */
    public function getField($primaryKey = true): array;

    /**
     * The attributes that should be hidden for arrays.
     * @return mixed
     */
    public function getHidden();

    /**
     * 获取数据类型转换
     * @return mixed
     */
    public function getCasts();

    /**
     * @Notes: 获取数据源
     * @Interface getConnections
     * @return string
     * @Author: TS
     * @Time: 2020-06-15   16:31
     */
    public function getConnections(): string;

    /**
     * 获取验证规则
     * @return array
     */
    public function getRule(): array;
    /**
     * 获取字段验证规则
     * @param string $attribute 字段名
     * @return string
     */
    public function getFieldRule(string $attribute):string;
    /**
     * 获取验证规则错误信息
     * @return array
     */
    public function getMessage(): array;
    /**
     * 获取字段说明列表
     * @return array
     */
    public function getAttributeAliasList(): array;
    /**
     * 获取字段说明
     * @param string $attribute 字段名
     * @return string
     */
    public function getAttributeAlias(string $attribute) :string;

    /**
     * 获取自动填充字段列表
     * @return array
     */
    public function getAutoFill(): array;
    /**
     * 获取自动处理字段列表
     * @return array
     */
    public function getAutoHandle(): array;

    /**
     * 获取UUid字段
     * @return string
     */
    public function getPrimaryUuidField(): string;

    /**
     * 获取表说明
     * @return string
     */
    public function getTableInfo(): string;

    /**
     * 获取允许模糊查询字段
     * @return array
     */
    public function getAllowFuzzySearch(): array;

    /**
     * 获取字段类型列表
     * @return array
     */
    public function getFieldTypeList(): array;
    /**
     * 获取字段类型
     * @param string $attribute 字段名
     * @return string
     */
    public function getFieldType(string $attribute): string;
    /**
     * 获取字段长度
     * @param string $attribute 字段名
     * @return int
     */
    public function getFieldLength(string $attribute): int;
    /**
     * 获取字段精度
     * @param string $attribute 字段名
     * @return int
     */
    public function getFieldPrecision(string $attribute): int;


    /**
     * 根据uuid获取单个实体
     * @param string $uuid
     * @param array $columns
     * Date : 2021/4/24 12:44
     * Author : 孤鸿渺影
     * @return Builder|Model|object|null
     */
    public function findUuid(string $uuid,array $columns = ['*']);

    /**
     * 根据Id或者uuid获取单个实体
     * @param $value
     * @param array $columns
     * Date : 2021/4/24 12:44
     * Author : 孤鸿渺影
     * @return Builder|Builder[]|\Illuminate\Database\Eloquent\Collection|Model|object|null
     */
    public function findIdOrUuid($value,array $columns = ['*']);
}