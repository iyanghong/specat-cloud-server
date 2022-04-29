<?php

namespace App\Http\Controllers\Member;

use App\Core\Enums\ErrorCode;
use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Member\UserRole;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * 用户角色
 * @date : 2021/5/8 23:11
 * @author : 孤鸿渺影
 * @package App\Http\Controllers\Member
 */
class UserRoleController  extends Controller
{

    /**
     * 保存用户角色
     * @param $userUuid
     * @date : 2021/5/8 23:11
     * @author : 孤鸿渺影
     * @return string
     * @throws Throwable
     */
    public function saveUserRole($userUuid): string
    {
        $list = request()->input('list');
        /* @var $userModel Builder */
        $userModel = new User();
        $user = $userModel->where(['user_uuid' => $userUuid])->first();
        if(!$user){
            return api_response_action(false,ErrorCode::$ENUM_NO_DATA_ERROR,'用户不存在');
        }
        $list = json_decode($list,true) ?? [];

        /* @var $userRoleModel Builder */
        $userRoleModel = new UserRole();
        $hasObjList = $userRoleModel->where([
            'user_uuid' => $userUuid
        ])->get();
        $hasList = [];
        foreach ($hasObjList as $item){
            $hasList[] = $item->role_uuid;
        }
        DB::beginTransaction();
        try {
            //解析需要添加的权限
            foreach ($list as $roleUuid) {
                if (!in_array($roleUuid, $hasList)) {
                    $addFlag = $userRoleModel->create([
                        'uuid' => getUuid(),
                        'user_uuid' => $userUuid,
                        'role_uuid' => $roleUuid,
                        'auth_time' => date('Y-m-d H:i:s')
                    ]);
                    if (!$addFlag) {
                        throw new \Exception("授权失败,error:create");
                    }
                }
            }

            //解析需要删除的权限
            foreach ($hasList as $roleUuid) {
                if (!in_array($roleUuid, $list)) {
                    $delFlag = $userRoleModel->where([
                        'user_uuid' => $userUuid,
                        'role_uuid' => $roleUuid,
                    ])->delete();
                    if (!$delFlag) {
                        throw new \Exception("授权失败,error:delete");
                    }
                }
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return api_response_exception($exception);
        }
        DB::commit();
        return api_response_action(true,ErrorCode::$ENUM_SUCCESS,'授权成功');
    }

    /**
     * 用户角色列表
     * @param $userUuid
     * @date : 2021/5/8 23:11
     * @author : 孤鸿渺影
     * @return string
     */
    public function listUserRole($userUuid): string
    {
        /* @var $userRoleModel Builder */
        $userRoleModel = new UserRole();
        $userRoles = $userRoleModel->where(['user_uuid' => $userUuid])->get();
        return api_response_show($userRoles);
    }
    /**
     * 列表
     * @Author:System Generate
     * @Date:2021-04-16 12:53:06
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new UserRole())->index();
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2021-04-16 12:53:06
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        return resourceConstructor(new UserRole())->store($request);
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2021-04-16 12:53:06
     * @param Request $request
     * @return string
     */
    public function get(Request $request): string
    {
        return resourceConstructor(new UserRole())->get($request);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2021-04-16 12:53:06
     * @param $id
     * @return string
     */
    public function show($id): string
    {
        return resourceConstructor(new UserRole())->show($id);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2021-04-16 12:53:06
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        return resourceConstructor(new UserRole())->update($request,$id);
    }

    /**
     * 删除
     * @Author:System Generate
     * @Date:2021-04-16 12:53:06
     * @param $id
     * @return string
     */
    public function destroy($id): string
    {
        return resourceConstructor(new UserRole())->destroy($id);
    }
}
