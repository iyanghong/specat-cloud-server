<?php

namespace App\Http\Controllers\Member;

use App\Core\Enums\ErrorCode;
use App\Http\Controllers\Controller;

use App\Models\Member\Role;
use App\Service\Auth\RoleAuthDevice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Member\Permission;
use Illuminate\Support\Facades\DB;

/**
 * 权限
 * @date : 2021/5/8 23:08
 * @author : 孤鸿渺影
 * @package App\Http\Controllers\Member
 */
class PermissionController  extends Controller
{

    /**
     * 获取角色权限列表
     * @param $uuid
     * @date : 2021/5/8 23:08
     * @author : 孤鸿渺影
     * @return string
     */
    public function getByRoleList($uuid): string
    {
        /* @var $permissionModel Builder */
        $permissionModel = new Permission();
        $hasObjList = $permissionModel->where([
            'role_uuid' => $uuid
        ])->get();
        $hasList = [];
        foreach ($hasObjList as $item){
            $hasList[] = $item->rule_uuid;
        }
        return api_response_show($hasList);
    }

    /**
     * 角色授权
     * @date : 2021/5/8 23:08
     * @author : 孤鸿渺影
     * @return string
     * @throws \Throwable
     */
    public function saveUserRoleList(): string
    {
        $list = request()->input('list');
        $roleUuid = request()->input('role_uuid');
        if(empty($roleUuid) || empty($list)){
            return api_response_action(false,ErrorCode::$ENUM_PARAM_NULL_ERROR);
        }
        /* @var $roleModel Builder */
        $roleModel = new Role();
        $role = $roleModel->where(['role_uuid' => $roleUuid])->first();
        if(!$role){
            return api_response_action(false,ErrorCode::$ENUM_NO_DATA_ERROR,'角色不存在');
        }
        $list = json_decode($list,true) ?? [];
        /* @var $permissionModel Builder */
        $permissionModel = new Permission();
        $hasObjList = $permissionModel->where(['role_uuid' => $roleUuid])->get();

        $hasList = [];
        foreach ($hasObjList as $item){
            $hasList[] = $item->rule_uuid;
        }
        DB::beginTransaction();
        try {
            //解析需要添加的权限
            foreach ($list as $ruleUuid) {
                if (!in_array($ruleUuid, $hasList)) {
                    $addFlag = $permissionModel->create([
                        'permission_uuid' => getUuid(),
                        'role_uuid' => $roleUuid,
                        'rule_uuid' => $ruleUuid,
                        'auth_time' => date('Y-m-d H:i:s')
                    ]);
                    if (!$addFlag) {
                        throw new \Exception("授权失败,error:create");
                    }
                }
            }

            //解析需要删除的权限
            foreach ($hasList as $ruleUuid) {
//                var_dump($hasList);
                if (!in_array($ruleUuid, $list)) {
                    $delFlag = $permissionModel->where([
                        'role_uuid' => $roleUuid,
                        'rule_uuid' => $ruleUuid,
                    ])->delete();
                    if (!$delFlag) {
                        throw new \Exception("授权失败,error:delete");
                    }
                }
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return api_response_action(false,ErrorCode::$ENUM_ACTION_ERROR,'授权失败,,error:exception');
        }
        $roleAuth = new RoleAuthDevice();
        $roleAuth->refreshRole($role['role_id']);
        DB::commit();
        return api_response_action(true,ErrorCode::$ENUM_SUCCESS,'授权成功');
    }
    /**
     * 列表
     * @Author:System Generate
     * @Date:2021-04-16 12:23:21
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new Permission())->index();
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2021-04-16 12:23:21
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        return resourceConstructor(new Permission())->store($request);
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2021-04-16 12:23:21
     * @param Request $request
     * @return string
     */
    public function get(Request $request): string
    {
        return resourceConstructor(new Permission())->get($request);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2021-04-16 12:23:21
     * @param $id
     * @return string
     */
    public function show($id): string
    {
        return resourceConstructor(new Permission())->show($id);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2021-04-16 12:23:21
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        return resourceConstructor(new Permission())->update($request,$id);
    }

    /**
     * 删除
     * @Author:System Generate
     * @Date:2021-04-16 12:23:21
     * @param $id
     * @return string
     */
    public function destroy($id): string
    {
        return resourceConstructor(new Permission())->destroy($id);
    }
}
