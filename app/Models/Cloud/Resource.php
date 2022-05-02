<?php

namespace App\Models\Cloud;

use App\Core\Constructors\Model\BaseModel as BaseModel;
use Illuminate\Support\Facades\DB;

class Resource extends BaseModel
{
    protected $table = 'resources';      //数据表名
    protected $primaryKey = 'id';        //表主键
    protected string $primaryUuidField = 'uuid'; //唯一标识字段
    public string $tableInfo = '资源表';        //表简介
    public $timestamps = true;        //是否自动处理时间字段
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = null;
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $fillable = ["uuid", "parent", "parent_all", "disk_uuid", "name", "type", "file_type", "file_extension", "size", "cover", "user_uuid", "create_user", "update_user"];

    public function child()
    {
        return $this->hasMany('App\Models\Cloud\Resource', 'parent', 'uuid')->select();
    }

    public function children()
    {
        return $this->child()->with('children');
    }

    /**
     *
     * @date : 2022/4/26 11:27
     * @return string|void
     * @author : 孤鸿渺影
     */
    public function getResourceDirectory($parentAll = '')
    {

        if (empty($parentAll)) $parentAll = $this->parent_all;
        if (!empty($parentAll)) {
            $path = [];
            $parentsUid = rtrim($parentAll, ",");
            $parentList = (new self())->whereIn('uuid', explode(',', $parentsUid))->orderBy('id', 'asc')->get();
            foreach ($parentList as $parentItem) {
                $path[] = $parentItem->name;
            }
            $path[] = $this->name . ($this->file_extension ? '.' . $this->file_extension : '');
            return implode('/', $path);
        }
        return '';
    }

    /**
     *
     * @date : 2022/4/26 22:49
     * @param $fileName
     * @param $fileExtension
     * @param bool $isFile
     * @return mixed
     * @author : 孤鸿渺影
     */
    public function getRepeatNameResource($fileName, $fileExtension, $isFile = true): mixed
    {
        $filter = [
            'parent' => $this->uuid,
            'name' => $fileName,
        ];
        if ($isFile) {
            $filter['file_extension'] = $fileExtension;
        } else {
            $filter['type'] = 'directory';
        }
        return $this->where($filter)->first();
    }

    /**
     *
     * @date : 2022/4/27 21:46
     * @param $fileName
     * @param $fileExtension
     * @param bool $isFile
     * @return mixed
     * @author : 孤鸿渺影
     */
    public function getCurrentDirectoryRepeatNameResource($fileName, $fileExtension, $isFile = true)
    {
        $filter = [
            'parent' => $this->parent,
            'name' => $fileName,
        ];
        if ($isFile) {
            $filter['file_extension'] = $fileExtension;
        } else {
            $filter['type'] = 'directory';
        }
        return $this->where($filter)->first();
    }

    /**
     *
     * @date : 2022/4/28 21:57
     * @return mixed
     * @author : 孤鸿渺影
     */
    public function getLocation(): mixed
    {

        $parentsUid = $this->parent_all ? rtrim($this->parent_all, ",") : '';
        $list = $this->whereIn('uuid', explode(',', $parentsUid))->orderBy('created_at', 'asc')->get(['uuid as resource_uuid', 'name', 'disk_uuid', 'created_at']);
        $list[] = [
            'resource_uuid' => $this->uuid,
            'name' => $this->name,
            'disk_uuid' => $this->disk_uuid,
        ];
        return $list->toArray();
    }


    /**
     * 验证规则列表
     * @var array
     */
    protected array $rule = [
        'uuid' => 'required',
        'disk_uuid' => 'required',
        'name' => 'required',
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
        'parent' => '父级',
        'parent_all' => '所有父级id',
        'disk_uuid' => '所属磁盘',
        'name' => '资源名',
        'type' => '资源类型',
        'file_type' => '文件类型',
        'file_extension' => '文件后缀',
        'size' => '资源大小',
        'cover' => '封面',
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
        'parent' => 'char:32,0',
        'parent_all' => 'text:0,0',
        'disk_uuid' => 'char:32,0',
        'name' => 'varchar:50,0',
        'type' => 'varchar:20,0',
        'size' => 'bigint:20,0',
        'cover' => 'varchar:200,0',
        'user_uuid' => 'char:32,0',
        'created_at' => 'datetime:0,0',
        'updated_at' => 'datetime:0,0',
        'create_user' => 'char:32,0',
        'update_user' => 'char:32,0',
    ];
}
