<?php

namespace App\Models\Member;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class RuleGroup extends BaseModel
{
	protected  $table = 'rule_group';      //数据表名
	protected $primaryKey = 'rule_group_id';        //表主键
	protected string $primaryUuidField = 'rule_group_uuid'; //唯一标识字段
	public string $tableInfo = '规则组';        //表简介
	public $timestamps = true;        //是否自动处理时间字段
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const DELETED_AT = null;
	protected $dateFormat = 'Y-m-d H:i:s';
	protected $fillable = ["rule_group_uuid","parent_uuid","rule_group_name","rule_group_info","create_user","update_user"];

    public function child(){
        return $this->hasMany('App\Models\Member\RuleGroup','parent_uuid','rule_group_uuid')
            ->select(['rule_group_id','rule_group_uuid','parent_uuid','rule_group_name']);
    }
    public function children(){
        return $this->child()->with('children');
    }

    public function ruleList(){

        return $this->hasMany('App\Models\Member\Rule','rule_group_uuid','rule_group_uuid')
            ->select(['rule_id','rule_uuid','rule_name','rule_group_uuid','type']);
    }
    public function allGroupRule(){
        return $this->child()->with(['allGroupRule','ruleList']);
    }

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'rule_group_uuid' => 'required',
		'rule_group_name' => 'required',
		'rule_group_info' => 'required',
		'create_user' => 'required',
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'rule_group_id' => '规则组编号',
		'rule_group_uuid' => '唯一标识',
		'parent_uuid' => '父级UUID',
		'rule_group_name' => '规则组名称',
		'rule_group_info' => '规则组简介',
		'created_at' => '添加时间',
		'updated_at' => '修改时间',
		'create_user' => '创建人',
		'update_user' => '修改人',
	];

	/**
	* 允许模糊查询列表
	* @var array
	*/
	protected array $allowFuzzySearch = ['rule_group_name'];

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
		'rule_group_uuid' => 'uuid',
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
		'rule_group_id' => 'int:11,0',
		'rule_group_uuid' => 'char:32,0',
		'parent_uuid' => 'char:32,0',
		'rule_group_name' => 'varchar:30,0',
		'rule_group_info' => 'varchar:200,0',
		'created_at' => 'datetime:0,0',
		'updated_at' => 'datetime:0,0',
		'create_user' => 'char:32,0',
		'update_user' => 'char:32,0',
	];
}
