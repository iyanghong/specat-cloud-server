<?php

namespace App\Http\Controllers\Member;

use App\Core\Enums\ErrorCode;
use App\Http\Controllers\Controller;

use App\Models\Member\Role;
use App\Service\Auth\FrontRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Member\Rule;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * 规则
 * @date : 2021/5/8 23:18
 * @author : 孤鸿渺影
 * @package App\Http\Controllers\Member
 */
class RuleController extends Controller
{

    /**
     * 刷新前端路由权限
     * @date : 2021/6/15 17:43
     * @return string
     * @author : 孤鸿渺影
     */
    public function batchResolveRoutes(): string
    {
        $list = json_decode(\request()->input('list'), true);
        if (empty($list)) {
            return api_response_action(false, ErrorCode::$ENUM_NO_DATA_ERROR);
        }
        $siteNameSuffix = \request()->input('siteNameSuffix', '');
        $FrontRule = new FrontRule();
        $FrontRule->setSiteNameSuffix($siteNameSuffix);
        $FrontRule->handle($list);
        $successNum = $FrontRule->getSuccessNum();
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, "成功{$successNum}个");
    }

    /**
     * 批量添加规则
     * @date : 2021/5/8 23:10
     * @return string
     * @throws Throwable
     * @author : 孤鸿渺影
     */
    public function batchInsert(): string
    {
        return resourceConstructor(new Rule())->batchInsert();
    }

    /**
     * 解析规则
     * @date : 2021/5/8 23:10
     * @return string
     * @author : 孤鸿渺影
     */
    public function analyseRule(): string
    {
        $content = request()->input('content');
        $format = request()->input('format');
        if (empty($content)) {
            return api_response_action(false, ErrorCode::$ENUM_NO_DATA_ERROR, '请输入内容');
        }
        if (!$format) $format = ' ';
        $list = [];

        if (stripos($content, 'Route::') !== false) {
            $listContent = explode("\n", $content);
            if (stripos($listContent[0], 'Route::group') !== false) {
                preg_match("/\['prefix'\s*=>\s*'(.*?)'\]/", $listContent[0], $prefix);
                $prefix = empty($prefix[1]) ? '' : $prefix[1];
                foreach ($listContent as $itemString) {
                    preg_match("/\(\s*'(.*?)'\s*,/", $itemString, $item);

                    if (!empty($item[1])) {
                        $list[] = [
                            'rule_name' => '',
                            'rule_code' => $prefix . $item[1],
                            'type' => 1,
                            'rule_io' => 'Global'

                        ];
                    }
                }
//                var_dump($prefix);
//                exit();
            }
        } else {
            $listContent = explode("\n", $content);
            foreach ($listContent as $itemString) {
                $itemData = explode($format, $itemString);
                $data = [
                    'rule_name' => $itemData[0] ?? '',
                    'rule_code' => empty($itemData[1]) ? '' : $itemData[1],
                    'type' => empty($itemData[2]) ? '1' : $itemData[2],
                    'rule_io' => empty($itemData[3]) ? 'Global' : $itemData[3],
                    'rule_info' => $itemData[0] ?? '',
                ];

                if (!empty($data['rule_code'])) {
                    $list[] = $data;
                }
            }
        }
        return api_response_show($list, ErrorCode::$ENUM_SUCCESS, '解析成功');
    }

    /**
     * 列表
     * @Author:System Generate
     * @Date:2021-04-16 12:16:29
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new Rule())->index();
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2021-04-16 12:16:29
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        return resourceConstructor(new Rule())->store($request);
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2021-04-16 12:16:29
     * @param Request $request
     * @return string
     */
    public function get(Request $request): string
    {
        return resourceConstructor(new Rule())->get($request);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2021-04-16 12:16:29
     * @param $id
     * @return string
     */
    public function show($id): string
    {
        return resourceConstructor(new Rule())->show($id);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2021-04-16 12:16:29
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        return resourceConstructor(new Rule())->update($request, $id);
    }

    /**
     * 删除
     * @Author:System Generate
     * @Date:2021-04-16 12:16:29
     * @param $id
     * @return string
     */
    public function destroy($id): string
    {
        return resourceConstructor(new Rule())->destroy($id);
    }

    /**
     * 刷新前端路由权限
     * @date : 2021/6/16 11:50
     * @author : 孤鸿渺影
     * @return string
     */
    public function refreshRouteRule()
    {
        $list = json_decode(\request()->input('list'), true);
        if (empty($list)) {
            return api_response_action(false, ErrorCode::$ENUM_NO_DATA_ERROR);
        }
        $siteNameSuffix = \request()->input('siteNameSuffix', '');
        $FrontRule = new FrontRule();
        $FrontRule->setSiteNameSuffix($siteNameSuffix);
        $FrontRule->handle($list);
        $successNum = $FrontRule->getSuccessNum();
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, "成功刷新{$successNum}个");
    }
}
