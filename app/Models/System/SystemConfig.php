<?php

namespace App\Models\System;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class SystemConfig extends BaseModel
{
	protected  $table = 'system_config';      //数据表名
	protected $primaryKey = 'id';        //表主键
	protected string $primaryUuidField = 'uuid'; //唯一标识字段
	public string $tableInfo = '系统配置';        //表简介
	public $timestamps = true;        //是否自动处理时间字段
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const DELETED_AT = null;
	protected $dateFormat = 'Y-m-d H:i:s';
	protected $fillable = ["uuid","model","name","code","value","info","type","content","create_user","update_user"];

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'uuid' => 'required',
		'name' => 'required',
		'code' => 'required',
		'value' => 'required',
		'type' => 'required',
		'create_user' => 'required'
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'id' => '编号',
		'uuid' => '唯一标识',
		'model' => '所属模块',
		'name' => '配置名',
		'code' => '配置标识',
		'value' => '配置值',
		'info' => '配置说明',
		'type' => '配置方式：1:打开模式，2:文本模式，3:选择模式,4:标签模式,5:加密文本,6:对象列表',
		'content' => '配置选项',
		'created_at' => '添加时间',
		'updated_at' => '修改时间',
		'create_user' => '创建人',
		'update_user' => '修改人',
	];

	/**
	* 允许模糊查询列表
	* @var array
	*/
	protected array $allowFuzzySearch = ['code','name'];

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
		'uuid' => 'uuid',
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
		'id' => 'int:11,0',
		'uuid' => 'char:32,0',
		'model' => 'varchar:100,0',
		'name' => 'varchar:50,0',
		'code' => 'varchar:50,0',
		'value' => 'text:0,0',
		'info' => 'varchar:200,0',
		'type' => 'tinyint:4,0',
		'content' => 'text:0,0',
		'created_at' => 'datetime:0,0',
		'updated_at' => 'datetime:0,0',
		'create_user' => 'char:32,0',
		'update_user' => 'char:32,0',
	];
}
