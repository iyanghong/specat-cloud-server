<?php


namespace App\Service\Auth;


//use App\Models\Auth\PermissionModel;
//use App\Models\Auth\RoleModel;
use App\Models\Member\Permission as PermissionModel;
use App\Models\Member\Role as RoleModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class RoleAuthDevice
{
    /**
     * @Notes:获取缓存
     * @Interface getByRedis
     * @param string $type
     * @param array $roleList
     * @return array
     * @Author: TS
     * @Time: 2020-12-01   17:33
     */
    public function getByRedis(string $type, array $roleList): array
    {
        $typeList = ['api', 'page'];
        if (!in_array($type, $typeList)) $type = 'api';
        $list = [];
        foreach ($roleList as $role_id) {
            $key = "RoleAuth:$type:$role_id";
            $data = json_decode(Cache::get($key), true);
            if ($data == null) {
                $refresh = $this->refreshRole($role_id);
                $data = empty($refresh[$type]) ? [] : $refresh;
            }
            $list = array_merge($list, $data);
        }
        return $list;
    }

    /**
     * @Notes:获取接口权限
     * @Interface getApiList
     * @param array $roleList
     * @return array
     * @Author: TS
     * @Time: 2020-12-02   18:54
     */
    public function getApiList(array $roleList): array
    {
        return $this->getByRedis('api', $roleList);
    }

    /**
     * @Notes:获取页面权限
     * @Interface getPage
     * @param array $roleList
     * @return array
     * @Author: TS
     * @Time: 2020-12-02   18:54
     */
    public function getPage(array $roleList): array
    {
        return $this->getByRedis('page', $roleList);
    }

    /**
     * @Notes:刷新角色权限
     * @Interface refresh
     * @param array $whereRole
     * @Author: TS
     * @Time: 2020-12-02   18:46
     */
    public function refresh(array $whereRole = [])
    {
        $roleModel = new RoleModel();
        $roleList = [];
        if (empty($whereRole)) {
            $roleList = $roleModel->get();
        } else {
            $roleList = $roleModel->whereIn('role_id', $whereRole)->get();
        }

        $permissionModel = new PermissionModel();
        foreach ($roleList as $role) {
            /* @var $permissionModel Builder */
            $this->getRuleQueryList($permissionModel, $role);
        }
    }

    /**
     * @Notes:刷新角色权限
     * @Interface refreshRole
     * @param $role_id
     * @return array[]
     * @Author: TS
     * @Time: 2020-12-02   18:46
     */
    public function refreshRole($role_id)
    {
        $roleModel = new RoleModel();
        /* @var $roleModel Builder */
        $role = $roleModel->where([
            'role_id' => $role_id
        ])->first();
        if (empty($role)) return ['page' => [], 'api' => []];
        $permissionModel = new PermissionModel();

        /* @var $permissionModel Builder */
        list($ruleApiList, $rulePageList) = $this->getRuleQueryList($permissionModel, $role);
        return ['page' => $rulePageList, 'api' => $ruleApiList];
    }

    /**
     *
     * @date : 2022/4/17 21:54
     * @param Builder $permissionModel
     * @param $role
     * @return array[]
     * @author : 孤鸿渺影
     */
    public function getRuleQueryList($permissionModel, $role): array
    {
        $ruleQueryList = $permissionModel
            ->join('rule', 'rule.rule_uuid', '=', 'permission.rule_uuid')
            ->where(['permission.role_uuid' => $role['role_uuid']])
            ->get(['rule.rule_code', 'rule.type', 'rule.rule_io']);

        $ruleApiList = [];
        $rulePageList = [];
        foreach ($ruleQueryList as $rule) {
            if ($rule['type'] == 1) {
                $ruleApiList[] = $rule['rule_code'];
            } elseif ($rule['type'] == 2) {
                $rulePageList[] = $rule['rule_code'];
            }
        }
        $apiKey = "RoleAuth:api:" . $role['role_id'];
        $pageKey = "RoleAuth:page:" . $role['role_id'];

        Cache::put($apiKey, json_encode($ruleApiList));
        Cache::put($pageKey, json_encode($rulePageList));
        return array($ruleApiList, $rulePageList);
    }

}
