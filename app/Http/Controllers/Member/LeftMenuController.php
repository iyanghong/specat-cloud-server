<?php

namespace App\Http\Controllers\Member;

use App\Core\Constructors\Model\BaseModel;
use App\Core\Enums\ErrorCode;
use App\Core\Generate\Resource\Model;
use App\Http\Controllers\Controller;

use App\Models\Member\RoleMenu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Member\LeftMenu;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * 菜单栏
 * @date : 2021/5/8 23:07
 * @author : 孤鸿渺影
 * @package App\Http\Controllers\Member
 */
class LeftMenuController  extends Controller
{

    /**
     * 列表
     * @Author:System Generate
     * @Date:2021-04-16 12:15:51
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new LeftMenu())->index();
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2021-04-16 12:15:51
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        return resourceConstructor(new LeftMenu())->store($request);
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2021-04-16 12:15:51
     * @param Request $request
     * @return string
     */
    public function get(Request $request): string
    {
        return resourceConstructor(new LeftMenu())->get($request,[
            'with' => 'parent'
        ]);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2021-04-16 12:15:51
     * @param $id
     * @return string
     */
    public function show($id): string
    {
        return resourceConstructor(new LeftMenu())->show($id);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2021-04-16 12:15:51
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        return resourceConstructor(new LeftMenu())->update($request,$id);
    }

    /**
     * 删除
     * @param $id
     * @date : 2021/5/8 23:07
     * @author : 孤鸿渺影
     * @return string
     * @throws Throwable
     */
    public function destroy($id): string
    {
        /* @var $leftMenuModel Builder */
        $leftMenuModel = new LeftMenu();
        $leftMenu = $leftMenuModel->find($id);
        if(!$leftMenu){
            return api_response_action(false,ErrorCode::$ENUM_NO_DATA_ERROR,'菜单不存在');
        }
        DB::beginTransaction();
        $deleteLeftMenu = $leftMenu->delete();
        if(!$deleteLeftMenu){
            DB::rollBack();
            return api_response_action(false,ErrorCode::$ENUM_ACTION_ERROR,'删除失败');
        }
        //删除本菜单所有关联关系
        /* @var $roleMenuModel Builder */
        $roleMenuModel = new RoleMenu();
        $roleMenuModel->where([
            'menu_uuid' => $leftMenu->menu_uuid
        ])->delete();


        $menuList = $leftMenuModel->where([
            'father' => $leftMenu->menu_uuid
        ])->get();
        foreach ($menuList as $menu) {
            $menu->delete();
            $roleMenuModel->where([
                'menu_uuid' => $menu->menu_uuid
            ])->delete();
        }

        DB::commit();
        return api_response_action(true);
    }


    /**
     * 获取树形列表
     * @date : 2021/5/8 23:08
     * @author : 孤鸿渺影
     * @return string
     */
    public function getTreeList(): string
    {
        /* @var $leftMenuModel Builder */
        $leftMenuModel = new LeftMenu();

        $leftMenus = $leftMenuModel
            ->with('children')
            ->where([
                'father' => null
            ])
            ->orderBy('weight','desc')
            ->get(['menu_id','menu_uuid','menu_name','menu_code','menu_icon','menu_view','father','weight']);

        return api_response_show($leftMenus->toArray());
    }
}
