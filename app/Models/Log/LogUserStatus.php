<?php

namespace App\Models\Log;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class LogUserStatus extends BaseModel
{
	protected  $table = 'log_user_status';      //数据表名
	protected $primaryKey = 'id';        //表主键
	protected string $primaryUuidField = 'status_uuid'; //唯一标识字段
	public string $tableInfo = '用户状态日志';        //表简介
	public $timestamps = true;        //是否自动处理时间字段
	const CREATED_AT = 'created_at';
	const UPDATED_AT = null;
	const DELETED_AT = null;
	protected $dateFormat = 'Y-m-d H:i:s';
	protected $fillable = ["status_uuid","user_uuid","content","ip","remark","recover","user_status","status_time"];

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'status_uuid' => 'required',
		'user_uuid' => 'required',
		'content' => 'required',
		'ip' => 'required|ip',
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'id' => '编号',
		'status_uuid' => '唯一标识',
		'user_uuid' => '用户编号',
		'content' => '状态内容',
		'ip' => '状态发生IP',
		'remark' => '备注',
		'recover' => '是否恢复',
		'user_status' => '用户状态，0:失效 2:冻结 3:违规',
		'created_at' => '添加时间',
		'status_time' => '状态持续时间(毫秒)',
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
		'status_uuid' => 'uuid',
		'ip' => 'ip',
		'created_at' => 'createTimeField:datetime',
	];

	/**
	* 自动处理字段类型
	* @var array
	*/
	protected $casts = [
		 'created_at' => 'date:Y-m-d H:i:s'
	];

	/**
	* 字段类型列表
	* @var array
	*/
	protected array $fieldTypes = [
		'id' => 'int:11,0',
		'status_uuid' => 'char:32,0',
		'user_uuid' => 'char:32,0',
		'content' => 'varchar:200,0',
		'ip' => 'varchar:100,0',
		'remark' => 'varchar:200,0',
		'recover' => 'tinyint:4,0',
		'user_status' => 'char:1,0',
		'created_at' => 'datetime:0,0',
		'status_time' => 'bigint:20,0',
	];
}