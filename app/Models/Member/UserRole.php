<?php

namespace App\Models\Member;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class UserRole extends BaseModel
{
	protected  $table = 'user_role';      //数据表名
	protected $primaryKey = 'id';        //表主键
	protected string $primaryUuidField = 'role_uuid'; //唯一标识字段
	public string $tableInfo = '用户角色';        //表简介
	public $timestamps = false; // 不自动管理时间
	protected $fillable = ["uuid","user_uuid","role_uuid","auth_time"];

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'uuid' => 'required',
		'user_uuid' => 'required',
		'role_uuid' => 'required',
		'auth_time' => 'unique',
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'id' => '编号',
		'uuid' => '唯一标识',
		'user_uuid' => '用户编号',
		'role_uuid' => '角色编号',
		'auth_time' => '添加时间',
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
		'role_uuid' => 'uuid',
	];

	/**
	* 字段类型列表
	* @var array
	*/
	protected array $fieldTypes = [
		'id' => 'int:11,0',
		'uuid' => 'char:32,0',
		'user_uuid' => 'char:32,0',
		'role_uuid' => 'char:32,0',
		'auth_time' => 'datetime:0,0',
	];
}