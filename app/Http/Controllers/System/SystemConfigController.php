<?php

namespace App\Http\Controllers\System;

use App\Core\Enums\ErrorCode;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\System\SystemConfig;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 系统配置
 * @date : 2021/5/8 23:15
 * @author : 孤鸿渺影
 * @package App\Http\Controllers\System
 */
class SystemConfigController extends Controller
{

    /**
     * 列表
     * @Author:System Generate
     * @Date:2021-04-16 12:19:29
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new SystemConfig())->index();
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2021-04-16 12:19:29
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        $systemConfigModel = new SystemConfig();
        $validator = $systemConfigModel->createValidate();
        if (!$validator->isSuccess()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->getMessage());
        }
        $data = $validator->getData();
        if ($data['type'] == 5) {
            $data['value'] = maskCrypt()->encrypt($data['value']);
        }
        /* @var $config Model */
        /* @var $systemConfigModel Builder */
        $config = $systemConfigModel->create($data);
        if ($config) {
            systemConfig()->refresh();
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '', [
                'id' => $config->getKey()
            ]);
        }
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR);
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2021-04-16 12:19:29
     * @param Request $request
     * @return string
     */
    public function get(Request $request): string
    {
        return resourceConstructor(new SystemConfig())->get($request);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2021-04-16 12:19:29
     * @param $id
     * @return string
     */
    public function show($id): string
    {
        return resourceConstructor(new SystemConfig())->show($id);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2021-04-16 12:19:29
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        $systemConfigModel = new SystemConfig();
        $validator = $systemConfigModel->updateValidator($id);
        if (!$validator->isSuccess()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->getMessage());
        }
        $data = $validator->getData();

        /* @var $config Model */

        $config = $systemConfigModel->findIdOrUuid($id);
        if (!$config) {
            return api_response_action(false, ErrorCode::$ENUM_NO_DATA_ERROR, "没有该配置项");
        }
        if ($data['type'] == 5 && $data['value'] != $config->value) {
            $data['value'] = maskCrypt()->encrypt($data['value']);
        }
        /* @var $systemConfigModel Builder */
        $flag = $config->update($data);
        if ($flag) {
            systemConfig()->refresh();//刷新缓存
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '修改成功');
        }
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR);
    }

    /**
     * 根据Code获取
     * @param $code
     * @date : 2021/5/8 23:13
     * @return string
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function getConfigByCode($code): string
    {
        $force = request()->input('force', false);     //强制从数据库获取
        if (!$force) {
            $config = systemConfig()->get($code);
            if ($config === null) {
                return api_response_show(null);
            }
            return api_response_show([
                'value' => $config
            ]);
        }
        $configModel = new SystemConfig();
        /* @var $configModel Builder */
        $config = $configModel->where([
            'code' => $code
        ])->first();

        if ($config) {
            $config = $config->toArray();
            if ($config['type'] === 5) {
                $config['value'] = maskCrypt()->decrypt($config['value']);
            }
            return api_response_show($config);
        }
        return api_response_show(null);
    }

    /**
     * 批量修改
     * @date : 2021/5/8 23:13
     * @return string
     * @author : 孤鸿渺影
     */
    public function batchUpdate(): string
    {
        $list = request()->input('list');
        $SystemConfigModel = new SystemConfig();
        $list = json_decode($list, true);
        if (empty($list)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_NULL_ERROR);
        }
        foreach ($list as $key => $item) {
            if ($item['type'] == 5) {
                $list[$key]['value'] = maskCrypt()->encrypt($item['value']);
            }
        }
        $flag = $SystemConfigModel->batch()->update($list);
        if ($flag) {
            systemConfig()->refresh();//刷新缓存
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '修改成功');
        }
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '修改失败');
    }

    /**
     * 删除
     * @Author:System Generate
     * @Date:2021-04-16 12:19:29
     * @param $id
     * @return string
     */
    public function destroy($id): string
    {
        return resourceConstructor(new SystemConfig())->destroy($id);
    }



}
