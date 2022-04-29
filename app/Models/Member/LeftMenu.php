<?php

namespace App\Models\Member;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class LeftMenu extends BaseModel
{
	protected  $table = 'left_menu';      //数据表名
	protected $primaryKey = 'menu_id';        //表主键
	protected string $primaryUuidField = 'menu_uuid'; //唯一标识字段
	public string $tableInfo = '导航菜单';        //表简介
	public $timestamps = true;        //是否自动处理时间字段
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const DELETED_AT = null;
	protected $dateFormat = 'Y-m-d H:i:s';
	protected $fillable = ["menu_uuid","menu_name","menu_code","menu_icon","menu_view","father","weight","create_user","update_user"];


    public function parent()
    {
        return $this->hasOne('App\Models\Member\LeftMenu','menu_uuid','father');
    }
    public function getChildren(){
        return $this->hasMany('App\Models\Member\LeftMenu','father','menu_uuid');
    }
    public function children(){
        return $this->getChildren()->with('children');
    }

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'menu_uuid' => 'required',
		'menu_name' => 'required',
		'create_user' => 'required',
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'menu_id' => '编号',
		'menu_uuid' => '唯一标识',
		'menu_name' => '菜单名称',
		'menu_code' => '菜单Code',
		'menu_icon' => '菜单图标',
		'menu_view' => '菜单路径',
		'father' => '唯一标识',
		'weight' => '权重',
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
		'menu_uuid' => 'uuid',
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
		'menu_id' => 'int:11,0',
		'menu_uuid' => 'char:32,0',
		'menu_name' => 'varchar:50,0',
		'menu_code' => 'varchar:100,0',
		'menu_icon' => 'varchar:100,0',
		'menu_view' => 'varchar:200,0',
		'father' => 'char:32,0',
		'weight' => 'int:11,0',
		'created_at' => 'datetime:0,0',
		'updated_at' => 'datetime:0,0',
		'create_user' => 'char:32,0',
		'update_user' => 'char:32,0',
	];
}
