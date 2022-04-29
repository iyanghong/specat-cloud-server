<?php

namespace App\Models\Member;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class RoleMenu extends BaseModel
{
	protected  $table = 'role_menu';      //数据表名
	protected $primaryKey = 'role_menu_id';        //表主键
	protected string $primaryUuidField = 'role_menu_uuid'; //唯一标识字段
	public string $tableInfo = '角色菜单';        //表简介
	public $timestamps = false; // 不自动管理时间
	protected $fillable = ["role_menu_uuid","menu_uuid","role_uuid","auth_time"];

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'role_menu_uuid' => 'required',
		'menu_uuid' => 'required',
		'role_uuid' => 'required',
		'auth_time' => 'required',
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'role_menu_id' => '编号',
		'role_menu_uuid' => '唯一标识',
		'menu_uuid' => '菜单唯一标识',
		'role_uuid' => '角色唯一标识',
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
		'role_menu_uuid' => 'uuid',
	];

	/**
	* 字段类型列表
	* @var array
	*/
	protected array $fieldTypes = [
		'role_menu_id' => 'int:11,0',
		'role_menu_uuid' => 'char:32,0',
		'menu_uuid' => 'char:32,0',
		'role_uuid' => 'char:32,0',
		'auth_time' => 'datetime:0,0',
	];
}