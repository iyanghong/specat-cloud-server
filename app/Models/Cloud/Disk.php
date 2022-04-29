<?php

namespace App\Models\Cloud;

use App\Core\Constructors\Model\BaseModel as BaseModel;
use App\Service\Disk\DiskNodeEnum;
use App\Service\Disk\VendorEnum;
use Illuminate\Database\Eloquent\Builder;

class Disk extends BaseModel
{
    protected $table = 'disk';      //数据表名
    protected $primaryKey = 'id';        //表主键
    protected string $primaryUuidField = 'uuid'; //唯一标识字段
    public string $tableInfo = '磁盘表';        //表简介
    public $timestamps = true;        //是否自动处理时间字段
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = null;
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $fillable = ["uuid", "vendor", "is_default", "name", "access_key_id", "access_key_secret", "max_size", "node", "bucket", "access_path", "base_path", "user_uuid", "create_user", "update_user"];

    /**
     *
     * @date : 2022/4/23 23:18
     * @param $value
     * @return VendorEnum|mixed
     * @author : 孤鸿渺影
     */
    public function getVendorNameAttribute($value)
    {
        return VendorEnum::getMessage($value);
    }

    /**
     *
     * @date : 2022/4/23 23:18
     * @param $value
     * @return VendorEnum|mixed
     * @author : 孤鸿渺影
     */
    public function getNodeNameAttribute($value)
    {
        return DiskNodeEnum::getMessage($value);
    }

    public function desktopResources()
    {
        return self::hasMany('App\Models\Cloud\Resource', 'disk_uuid', 'uuid')->where(['is_default' => 1]);
    }

    /**
     *
     * 获取用户的硬盘数量
     * @date : 2022/4/24 15:58
     * @param $userUid
     * @return int
     * @author : 孤鸿渺影
     */
    public function getUserCount($userUid): int
    {
        /** @var $this Builder */
        return $this->where([
            'user_uuid' => $userUid
        ])->count();
    }

    /**
     * 验证规则列表
     * @var array
     */
    protected array $rule = [
        'uuid' => 'required',
        'name' => 'required',
        'base_path' => 'required',
        'user_uuid' => 'required',
        'access_path' => 'required',
        'create_user' => 'required'
    ];

    /**
     * 字段中文说明列表
     * @var array
     */
    protected array $attributeAlias = [
        'id' => '编号',
        'uuid' => '唯一标识',
        'vendor' => '磁盘提供商、-1:当前服务器',
        'is_default' => '是否默认，0-否 1-是',
        'name' => '磁盘名称',
        'access_key_id' => '访问ID',
        'access_key_secret' => '访问密钥',
        'max_size' => '最大大小',
        'node' => '磁盘所属节点',
        'bucket' => '存储桶',
        'access_path' => '访问路径',
        'base_path' => '磁盘根路径',
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
        'vendor' => 'varchar:20,0',
        'is_default' => 'tinyint:4,0',
        'name' => 'varchar:30,0',
        'access_key_id' => 'varchar:255,0',
        'access_key_secret' => 'varchar:255,0',
        'max_size' => 'bigint:20,0',
        'node' => 'varchar:100,0',
        'bucket' => 'varchar:100,0',
        'base_path' => 'varchar:255,0',
        'user_uuid' => 'char:32,0',
        'created_at' => 'datetime:0,0',
        'updated_at' => 'datetime:0,0',
        'create_user' => 'char:32,0',
        'update_user' => 'char:32,0',
    ];
}
