<?php


namespace App\Service\Auth;


use App\Models\Member\Permission as PermissionModel;
use App\Models\Member\Role as RoleModel;
use App\Models\Member\RoleMenu as RoleMenuModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class RoleMenuDevice
{
    /**
     * @Notes:获取缓存
     * @Interface getByRedis
     * @param array $roleList
     * @return array
     * @Author: TS
     * @Time: 2020-12-01   17:33
     */
    public function getByRedis(array $roleList)
    {
        $list = [];
        foreach ($roleList as $role_id) {
            $key = "RoleMenu:$role_id";
            $data = json_decode(Cache::get($key), true);
            if ($data == null) {
                $data = $this->refreshRole($role_id);
            }
            $list = $this->mergeMenu($list, $data ?? []);
        }
        usort($list, function ($oba, $obb) {
            if ($oba['weight'] < $obb['weight']) {
                return 1;
            } elseif ($oba['weight'] == $obb['weight']) {
                return ($oba['menu_id'] > $obb['menu_id']) ? 1 : -1;
            }
            return 0;
        });

        return $list;
    }

    /**
     * @Notes:合并导航
     * @Interface mergeMenu
     * @Author: TS
     * @Time: 2020-12-01   20:22
     */
    private function mergeMenu($main, $data)
    {
        foreach ($data as $item) {
            if (!in_array($item, $main)) {
                $main[] = $item;
            }
        }
        return $main;
    }

    /**
     * @Notes:刷新缓存
     * @Interface refresh
     * @param array $whereRole
     * @Author: TS
     * @Time: 2020-12-02   18:33
     */
    public function refresh(array $whereRole = [])
    {
        /* @var $roleModel Builder */
        $roleModel = new RoleModel();
        $roleList = [];
        if (empty($whereRole)) {
            $roleList = $roleModel->get();
        } else {
            $roleList = $roleModel->whereIn('role_id', $whereRole)->get();
        }
        /* @var $roleMenuModel Builder */
        $roleMenuModel = new RoleMenuModel();
        foreach ($roleList as $role) {
            $roleMenuList = $roleMenuModel
                ->join('left_menu', 'left_menu.menu_uuid', '=', 'role_menu.menu_uuid')
                ->where('role_uuid', $role['role_uuid'])
                ->orderBy('weight', 'desc')
                ->get([
                    'left_menu.menu_id',
                    'left_menu.menu_uuid',
                    'left_menu.menu_name',
                    'left_menu.menu_icon',
                    'left_menu.menu_view',
                    'left_menu.menu_code',
                    'left_menu.father',
                    'left_menu.weight'
                ]);
            $key = "RoleMenu:" . $role['role_id'];
            Cache::set($key, json_encode($roleMenuList));
        }

    }

    /**
     * @Notes:刷新单个角色缓存
     * @Interface refreshRole
     * @param $role_id
     * @Author: TS
     * @Time: 2020-12-01   17:31
     */
    public function refreshRole($role_id)
    {
        /* @var $roleModel Builder */
        $roleModel = new RoleModel();
        $role = $roleModel->find($role_id);
        if (empty($role)) return;
        /* @var $roleMenuModel Builder */
        $roleMenuModel = new RoleMenuModel();
        $roleMenuList = $roleMenuModel
            ->join('left_menu', 'left_menu.menu_uuid', '=', 'role_menu.menu_uuid')
            ->where('role_uuid', $role['role_uuid'])
            ->orderBy('weight', 'desc')
            ->get([
                'left_menu.menu_id',
                'left_menu.menu_uuid',
                'left_menu.menu_name',
                'left_menu.menu_icon',
                'left_menu.menu_view',
                'left_menu.menu_code',
                'left_menu.father',
                'left_menu.weight'
            ]);
        $key = "RoleMenu:" . $role['role_id'];
        Cache::set($key, json_encode($roleMenuList));
        return $roleMenuList;
    }

    /**
     * @Notes:获取用户菜单导航
     * @Interface getUserMenu
     * @param array $roleList
     * @return array
     * @Author: TS
     * @Time: 2020-12-01   20:45
     */
    public function getUserMenu(array $roleList)
    {
        $list = $this->getByRedis($roleList);
        if (empty($list)) return [];
        $fatherList = [];
        foreach ($list as $key => $item) {
            if (empty($item['father'])) {
                $item['children'] = [];
                $fatherList[] = $item;
                unset($list[$key]);
            }
        }

        for ($i = 0; $i < sizeof($fatherList); $i++) {
            foreach ($list as $item) {
                if ($item['father'] === $fatherList[$i]['menu_uuid']) {
                    $fatherList[$i]['children'][] = $item;
                }
            }

        }

        return $fatherList;
    }
}