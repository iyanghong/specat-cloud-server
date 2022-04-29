<?php

namespace App\Models\Cloud;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class ResourcesShare extends BaseModel
{
	protected  $table = 'resources_share';      //数据表名
	protected $primaryKey = 'id';        //表主键
	protected string $primaryUuidField = 'uuid'; //唯一标识字段
	public string $tableInfo = '资源分享表';        //表简介
	public $timestamps = true;        //是否自动处理时间字段
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const DELETED_AT = null;
	protected $dateFormat = 'Y-m-d H:i:s';
	protected $fillable = ["uuid","resources_uuid","is_public","secret_key","expiration","share_time","user_uuid","create_user","update_user"];

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'uuid' => 'required',
		'resources_uuid' => 'required',
		'share_time' => 'required',
		'user_uuid' => 'required',
		'create_user' => 'required'
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'id' => '编号',
		'uuid' => '唯一标识',
		'resources_uuid' => '资源标识',
		'is_public' => '是否公开',
		'secret_key' => '访问密钥',
		'expiration' => '过期时间：0-永久',
		'share_time' => '分享时间，用于防止二次分享标识',
		'user_uuid' => '所属用户',
		'created_at' => '添加时间',
		'updated_at' => '修改时间',
		'create_user' => '创建人',
		'update_user' => '修改人',
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
        'uuid' => 'uuid',
        'user_uuid' => 'createUserField:uuid',
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
		'resources_uuid' => 'char:32,0',
		'is_public' => 'tinyint:4,0',
		'secret_key' => 'varchar:50,0',
		'expiration' => 'bigint:20,0',
		'share_time' => 'datetime:0,0',
		'user_uuid' => 'char:32,0',
		'created_at' => 'datetime:0,0',
		'updated_at' => 'datetime:0,0',
		'create_user' => 'char:32,0',
		'update_user' => 'char:32,0',
	];
}
