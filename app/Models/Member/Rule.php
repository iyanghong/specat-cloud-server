<?php

namespace App\Models\Member;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class Rule extends BaseModel
{
	protected  $table = 'rule';      //数据表名
	protected $primaryKey = 'rule_id';        //表主键
	protected string $primaryUuidField = 'rule_uuid'; //唯一标识字段
	public string $tableInfo = '规则';        //表简介
	public $timestamps = true;        //是否自动处理时间字段
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const DELETED_AT = null;
	protected $dateFormat = 'Y-m-d H:i:s';
	protected $fillable = ["rule_uuid","rule_name","rule_info","rule_code","rule_group_uuid","type","rule_io","create_user","update_user"];

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'rule_uuid' => 'required',
		'rule_name' => 'required',
		'rule_info' => 'required',
		'rule_code' => 'required',
		'rule_group_uuid' => 'required',
		'create_user' => 'required'
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'rule_id' => '规则编号',
		'rule_uuid' => '唯一标识',
		'rule_name' => '规则名',
		'rule_info' => '规则介绍',
		'rule_code' => '规则代码',
		'rule_group_uuid' => '规则组标识',
		'type' => '限制类型,1:接口 2:页面',
		'rule_io' => '限制端',
		'created_at' => '添加时间',
		'updated_at' => '修改时间',
		'create_user' => '创建人',
		'update_user' => '修改人',
	];

	/**
	* 允许模糊查询列表
	* @var array
	*/
	protected array $allowFuzzySearch = ['rule_name','rule_code'];

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
		'rule_uuid' => 'uuid',
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
		'rule_id' => 'int:11,0',
		'rule_uuid' => 'char:32,0',
		'rule_name' => 'varchar:30,0',
		'rule_info' => 'varchar:200,0',
		'rule_code' => 'varchar:200,0',
		'rule_group_uuid' => 'char:32,0',
		'type' => 'int:11,0',
		'rule_io' => 'varchar:30,0',
		'created_at' => 'datetime:0,0',
		'updated_at' => 'datetime:0,0',
		'create_user' => 'char:32,0',
		'update_user' => 'char:32,0',
	];
}
