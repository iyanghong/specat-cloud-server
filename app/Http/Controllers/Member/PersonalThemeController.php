<?php

namespace App\Http\Controllers\Member;

use App\Core\Enums\ErrorCode;
use App\Exceptions\NoLoginException;
use App\Http\Controllers\Controller;

use App\Service\Disk\Config\DiskConfig;
use App\Service\Disk\DiskFactory;
use Illuminate\Http\Request;
use App\Models\Member\PersonalTheme;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 主题设置
 * @date : 2022/5/1 22:37
 * @author : 孤鸿渺影
 */
class PersonalThemeController extends Controller
{

    /**
     * 获取当前用户个性化主题
     * @date : 2022/5/1 22:42
     * @return string
     * @throws NoLoginException
     * @author : 孤鸿渺影
     */
    public function getOnlineSetting(): string
    {
        onlineMember()->loginIntercept();
        $model = new PersonalTheme();
        $setting = $model->where([
            'user_uuid' => onlineMember()->getUuid()
        ])->first();
        if (!$setting) {
            $setting = $model->create([
                'uuid' => getUuid(),
                'user_uuid' => onlineMember()->getUuid(),
                'create_user' => onlineMember()->getUuid()
            ]);
            $setting->refresh();
        }

        return api_response_show($setting);
    }

    /**
     * 修改设置
     * @throws NoLoginException
     * @throws InvalidArgumentException
     */
    public function updateSetting(): string
    {
        onlineMember()->loginIntercept();
        $data = \request()->only(['background_image']);
        if (empty($data)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR);
        }
        $model = new PersonalTheme();
        $setting = $model->where([
            'user_uuid' => onlineMember()->getUuid()
        ])->first();
        if (!$setting) {
            $setting = $model->create([
                'uuid' => getUuid(),
                'user_uuid' => onlineMember()->getUuid(),
                'create_user' => onlineMember()->getUuid()
            ]);
            $setting->refresh();
        }
        if (!empty($data['background_image'])) {
            if (str_contains($data['background_image'], 'cache/image')) {
                $path = 'specat-cloud/users/' . onlineMember()->getId() . '/background-image/' . getUuid() . '.' . explode('.', $data['background_image'])[1];
                $diskConfig = new DiskConfig();
                $diskConfig->setSystemDisk();
                $disk = DiskFactory::build($diskConfig);
                $disk->move($data['background_image'], $path);
                $data['background_image'] = $path;
            }
//            $setting->background_image = $path;
        }
        $flag = $setting->update($data);
        if ($flag) {
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '修改成功', $setting->toArray());
        }
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '修改失败');

    }

    /**
     * 列表
     * @Author:System Generate
     * @Date:2022-05-01 22:37:04
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new PersonalTheme())->index();
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2022-05-01 22:37:04
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        return resourceConstructor(new PersonalTheme())->store($request);
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2022-05-01 22:37:04
     * @param Request $request
     * @return string
     */
    public function get(Request $request): string
    {
        return resourceConstructor(new PersonalTheme())->get($request);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2022-05-01 22:37:04
     * @param $id
     * @return string
     */
    public function show($id): string
    {
        return resourceConstructor(new PersonalTheme())->show($id);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2022-05-01 22:37:04
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        return resourceConstructor(new PersonalTheme())->update($request, $id);
    }

    /**
     * 删除
     * @Author:System Generate
     * @Date:2022-05-01 22:37:04
     * @param $id
     * @return string
     */
    public function destroy($id): string
    {
        return resourceConstructor(new PersonalTheme())->destroy($id);
    }
}
