<?php

namespace App\Models;

use App\Core\Constructors\Model\BaseModel as BaseModel;

class User extends BaseModel
{
	protected  $table = 'users';      //数据表名
	protected $primaryKey = 'user_id';        //表主键
	protected string $primaryUuidField = 'user_uuid'; //唯一标识字段
	public string $tableInfo = '用户表';        //表简介
	public $timestamps = true;        //是否自动处理时间字段
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const DELETED_AT = null;
	protected $dateFormat = 'Y-m-d H:i:s';
	protected $fillable = ["user_uuid","user_pwd","user_name","user_phone","user_email","user_sex","user_birthday","user_header","user_info","user_address","score","user_status","fans","concern","error_num","status_uuid","qq","wechat","weibo","user_ip","login_expire","login_time","create_source","update_user"];

	/**
	* 验证规则列表
	* @var array
	*/
	protected array $rule = [
		'user_uuid' => 'required',
		'user_pwd' => 'required',
		'user_name' => 'required',
		'user_phone' => 'phone',
		'user_email' => 'email',
		'user_header' => 'required',
		'user_ip' => 'required|ip',
	];

	/**
	* 字段中文说明列表
	* @var array
	*/
	protected array $attributeAlias = [
		'user_id' => '用户编号',
		'user_uuid' => '唯一标识',
		'user_pwd' => '登录密码',
		'user_name' => '用户昵称',
		'user_phone' => '手机号码',
		'user_email' => '邮箱号码',
		'user_sex' => '用户性别',
		'user_birthday' => '用户生日',
		'user_header' => '用户头像',
		'user_info' => '个性签名',
		'user_address' => '用户地址',
		'score' => '积分',
		'user_status' => '用户状态,0:失效 1:正常 2:冻结 3:违规 4:注销中',
		'fans' => '粉丝数',
		'concern' => '关注数',
		'error_num' => '密码错误次数',
		'status_uuid' => '状态编号',
		'qq' => 'QQ号',
		'wechat' => '微信号',
		'weibo' => '微博号',
		'user_ip' => '用户IP',
		'login_expire' => '登陆过期时间',
		'login_time' => '登录时间',
		'created_at' => '添加时间',
		'updated_at' => '修改时间',
		'create_source' => '注册来源',
		'update_user' => '修改人',
	];

	/**
	* 允许模糊查询列表
	* @var array
	*/
	protected array $allowFuzzySearch = ['user_name'];

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
		'user_uuid' => 'uuid',
		'user_ip' => 'ip',
		'created_at' => 'createTimeField:datetime',
		'updated_at' => 'updateTimeField:datetime',
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
		'user_id' => 'int:11,0',
		'user_uuid' => 'char:32,0',
		'user_pwd' => 'varchar:100,0',
		'user_name' => 'varchar:20,0',
		'user_phone' => 'char:11,0',
		'user_email' => 'varchar:30,0',
		'user_sex' => 'tinyint:4,0',
		'user_birthday' => 'bigint:20,0',
		'user_header' => 'varchar:200,0',
		'user_info' => 'varchar:200,0',
		'user_address' => 'int:11,0',
		'score' => 'bigint:20,0',
		'user_status' => 'tinyint:4,0',
		'fans' => 'bigint:20,0',
		'concern' => 'bigint:20,0',
		'error_num' => 'int:11,0',
		'status_uuid' => 'varchar:32,0',
		'qq' => 'int:11,0',
		'wechat' => 'varchar:30,0',
		'weibo' => 'varchar:30,0',
		'user_ip' => 'varchar:20,0',
		'login_expire' => 'int:11,0',
		'login_time' => 'datetime:0,0',
		'created_at' => 'datetime:0,0',
		'updated_at' => 'datetime:0,0',
		'create_source' => 'varchar:20,0',
		'update_user' => 'char:32,0',
	];
}
