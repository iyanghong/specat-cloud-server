<?php

namespace App\Http\Controllers\Member;

use App\Core\Constructors\Controller\FilterConstructor;
use App\Core\Enums\ErrorCode;
use App\Http\Controllers\Controller;

use App\Models\Member\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Member\RoleMenu;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * 角色菜单
 * @date : 2021/5/8 23:09
 * @author : 孤鸿渺影
 * @package App\Http\Controllers\Member
 */
class RoleMenuController extends Controller
{

    /**
     * 获取角色菜单列表
     * @param $uuid
     * @date : 2021/5/8 23:09
     * @return string
     * @throws \ErrorException
     * @author : 孤鸿渺影
     */
    public function getRoleMenuListByRole($uuid): string
    {

        $roleMenuModel = new RoleMenu();
        $filter = new FilterConstructor($roleMenuModel, \request());
        $roleMenus = $filter->filter(['role_uuid' => $uuid])->get();
        return api_response_show($roleMenus);
    }

    /**
     * 角色授权
     * @Date : 2021/4/20 22:36
     * @Author : 孤鸿渺影
     * @return string
     * @throws Throwable
     */
    public function saveRoleMenuList(): string
    {
        $list = request()->input('list');
        $roleUuid = request()->input('role_uuid');
        if (empty($roleUuid)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_NULL_ERROR);
        }
        /* @var $roleModel Builder */
        $roleModel = new Role();
        $role = $roleModel->where([
            'role_uuid' => $roleUuid
        ])->first();
        if (empty($role)) {
            return api_response_action(false, ErrorCode::$ENUM_NO_DATA_ERROR, '角色不存在');
        }
        $list = json_decode($list, true) ?? [];
        /* @var $roleMenuModel Builder */
        $roleMenuModel = new RoleMenu();
        $hasObjList = $roleMenuModel->where([
            'role_uuid' => $roleUuid
        ])->get();
        $hasList = [];
        foreach ($hasObjList as $item) {
            $hasList[] = $item->menu_uuid;
        }
        DB::beginTransaction();
        try {
            //解析需要添加的权限
            foreach ($list as $menuUuid) {
                if (!in_array($menuUuid, $hasList)) {
                    $addFlag = $roleMenuModel->create([
                        'role_menu_uuid' => getUuid(),
                        'role_uuid' => $roleUuid,
                        'menu_uuid' => $menuUuid,
                        'auth_time' => date('Y-m-d H:i:s')
                    ]);
                    if (!$addFlag) {
                        throw new \Exception("授权失败,error:create");
                    }
                }
            }

            //解析需要删除的权限
            foreach ($hasList as $menuUuid) {
//                var_dump($hasList);
                if (!in_array($menuUuid, $list)) {
                    $delFlag = $roleMenuModel->where([
                        'role_uuid' => $roleUuid,
                        'menu_uuid' => $menuUuid,
                    ])->delete();
                    if (!$delFlag) {
                        throw new \Exception("授权失败,error:delete");
                    }
                }
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '授权失败,,error:exception');
        }
        DB::commit();
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '授权成功');


    }

    /**
     * 列表
     * @Author:System Generate
     * @Date:2021-04-16 12:24:28
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new RoleMenu())->index();
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2021-04-16 12:24:28
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        return resourceConstructor(new RoleMenu())->store($request);
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2021-04-16 12:24:28
     * @param Request $request
     * @return string
     */
    public function get(Request $request)
    {
        return resourceConstructor(new RoleMenu())->get($request);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2021-04-16 12:24:28
     * @param $id
     * @return string
     */
    public function show($id): string
    {
        return resourceConstructor(new RoleMenu())->show($id);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2021-04-16 12:24:28
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        return resourceConstructor(new RoleMenu())->update($request, $id);
    }

    /**
     * 删除
     * @Author:System Generate
     * @Date:2021-04-16 12:24:28
     * @param $id
     * @return string
     */
    public function destroy($id): string
    {
        return resourceConstructor(new RoleMenu())->destroy($id);
    }
}
