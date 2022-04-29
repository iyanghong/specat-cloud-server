<?php

namespace App\Models\Member;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class Permission extends BaseModel
{
	protected  $table = 'permission';      //数据表名
	protected $primaryKey = 'permission_id';        //表主键
	protected string $primaryUuidField = 'permission_uuid'; //唯一标识字段
	public string $tableInfo = '权限表';        //表简介
	public $timestamps = false; // 不自动管理时间
	protected $fillable = ["permission_uuid","role_uuid","rule_uuid","auth_time"];

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'permission_uuid' => 'required',
		'role_uuid' => 'required',
		'rule_uuid' => 'required',
		'auth_time' => 'required',
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'permission_id' => '编号',
		'permission_uuid' => '唯一标识',
		'role_uuid' => '角色',
		'rule_uuid' => '规则',
		'auth_time' => '授权时间',
	];

	/**
	* 允许模糊查询列表
	* @var array
	*/
	protected array $allowFuzzySearch = [];

	/**
	* 自动处理字段列表
	* @var array
	*/
	protected array $autoHandle = [];

	/**
	* 自动填充字段内容列表
	* @var array
	*/
	protected array $autoFill = [
		'permission_uuid' => 'uuid',
	];

	/**
	* 字段类型列表
	* @var array
	*/
	protected array $fieldTypes = [
		'permission_id' => 'int:11,0',
		'permission_uuid' => 'char:32,0',
		'role_uuid' => 'char:32,0',
		'rule_uuid' => 'char:32,0',
		'auth_time' => 'datetime:0,0',
	];
}