<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;

use ErrorException;
use Illuminate\Http\Request;
use App\Models\Member\RuleGroup;
use Illuminate\Http\Response;

/**
 * 规则组
 * @date : 2021/5/8 23:10
 * @author : 孤鸿渺影
 * @package App\Http\Controllers\Member
 */
class RuleGroupController extends Controller
{

    /**
     * 树形列表
     * @date : 2021/5/8 23:10
     * @return string
     * @throws ErrorException
     * @author : 孤鸿渺影
     */
    public function listTree(): string
    {
        $model = filterConstructor(new RuleGroup(), ['with' => 'children'])->filter(['parent_uuid' => null]);
        $groups = $model->get();
        return api_response_show($groups);
    }

    /**
     * 规则树形列表
     * @date : 2021/5/8 23:10
     * @return string
     * @throws ErrorException
     * @author : 孤鸿渺影
     */
    public function listRuleTree(): string
    {
        $model = filterConstructor(new RuleGroup(), ['with' => ['ruleList', 'allGroupRule']])->filter(['parent_uuid' => null]);
        $list = $model->get(['rule_group_id', 'rule_group_uuid', 'parent_uuid', 'rule_group_name']);
        return api_response_show($list);
    }

    /**
     * 列表
     * @Author:System Generate
     * @Date:2021-04-16 12:16:20
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new RuleGroup())->index();
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2021-04-16 12:16:20
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        return resourceConstructor(new RuleGroup())->store($request);
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2021-04-16 12:16:20
     * @param Request $request
     * @return string
     */
    public function get(Request $request): string
    {
        return resourceConstructor(new RuleGroup())->get($request);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2021-04-16 12:16:20
     * @param $id
     * @return string
     */
    public function show($id): string
    {
        return resourceConstructor(new RuleGroup())->show($id);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2021-04-16 12:16:20
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        return resourceConstructor(new RuleGroup())->update($request, $id);
    }

    /**
     * 删除
     * @Author:System Generate
     * @Date:2021-04-16 12:16:20
     * @param $id
     * @return string
     */
    public function destroy($id): string
    {
        return resourceConstructor(new RuleGroup())->destroy($id);
    }
}
