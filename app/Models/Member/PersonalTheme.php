<?php

namespace App\Models\Member;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class PersonalTheme extends BaseModel
{
	protected  $table = 'personal_theme';      //数据表名
	protected $primaryKey = 'id';        //表主键
	protected string $primaryUuidField = 'uuid'; //唯一标识字段
	public string $tableInfo = '个性化主题表';        //表简介
	public $timestamps = true;        //是否自动处理时间字段
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const DELETED_AT = null;
	protected $dateFormat = 'Y-m-d H:i:s';
	protected $fillable = ["uuid","background_image","user_uuid","create_user","update_user"];

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'uuid' => 'required',
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
		'background_image' => '背景图片',
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
		'background_image' => 'varchar:200,0',
		'user_uuid' => 'char:32,0',
		'created_at' => 'datetime:0,0',
		'updated_at' => 'datetime:0,0',
		'create_user' => 'char:32,0',
		'update_user' => 'char:32,0',
	];
}
