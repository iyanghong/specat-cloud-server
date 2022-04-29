<?php

namespace App\Models\Member;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class Role extends BaseModel
{
	protected  $table = 'role';      //数据表名
	protected $primaryKey = 'role_id';        //表主键
	protected string $primaryUuidField = 'role_uuid'; //唯一标识字段
	public string $tableInfo = '角色表';        //表简介
	public $timestamps = true;        //是否自动处理时间字段
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const DELETED_AT = null;
	protected $dateFormat = 'Y-m-d H:i:s';
	protected $fillable = ["role_uuid","role_name","role_info","create_user","update_user"];

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'role_uuid' => 'required',
		'role_name' => 'required',
		'role_info' => 'required',
		'create_user' => 'required',
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'role_id' => '角色编号',
		'role_uuid' => '唯一标识',
		'role_name' => '角色名称',
		'role_info' => '角色简介',
		'created_at' => '添加时间',
		'updated_at' => '修改时间',
		'create_user' => '创建人',
		'update_user' => '修改人',
	];

	/**
	* 允许模糊查询列表
	* @var array
	*/
	protected array $allowFuzzySearch = ['role_name'];

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
		'created_at' => 'createTimeField:datetime',
		'updated_at' => 'updateTimeField:datetime',
		'create_user' => 'createUserField:uuid',
		'update_user' => 'updateUserField:uuid',
	];

	/**
	* 自动处理字段类型
	* @var array
	*/
	protected $casts = [
		 'created_at' => 'date:Y-m-d H:i:s',
		 'updated_at' => 'date:Y-m-d H:i:s'
	];

	/**
	* 字段类型列表
	* @var array
	*/
	protected array $fieldTypes = [
		'role_id' => 'int:11,0',
		'role_uuid' => 'char:32,0',
		'role_name' => 'varchar:30,0',
		'role_info' => 'varchar:200,0',
		'created_at' => 'datetime:0,0',
		'updated_at' => 'datetime:0,0',
		'create_user' => 'char:32,0',
		'update_user' => 'char:32,0',
	];
}
