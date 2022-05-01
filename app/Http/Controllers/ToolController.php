<?php

namespace App\Http\Controllers;

use App\Core\Enums\ErrorCode;
use App\Service\Disk\Config\DiskConfig;
use App\Service\Disk\DiskFactory;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 孤鸿渺影
 * 2022/5/1 19:05
 * ToolController
 */
class ToolController extends Controller
{

    /**
     * 上传缓存图片
     * @throws InvalidArgumentException
     */
    public function uploadCacheImage(): string
    {
        if (!onlineMember()->isLogin()) {
            return api_response_action(false, ErrorCode::$ENUM_NO_LOGIN_ERROR);
        }
        $file = request()->file('file', null);
        if ($file == null) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_NULL_ERROR, '请上传文件');
        }
        $fileExtension = $file->getClientOriginalExtension();
        if (!in_array($fileExtension, ['png', 'jpeg', 'jpg'])) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '不是图片类型');
        }
        $maxSize = 2097152;
        if ($file->getSize() > $maxSize) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '大小不能超过' . round($maxSize / 1024 / 1024, 2) . "MB");
        }
        $suffix = time() . '-' . getUuid() . '.' . $file->getClientOriginalExtension();
        $diskConfig = new DiskConfig();
        $diskConfig->setSystemDisk();
        $path = 'cache/images/' . $suffix;
        $disk = DiskFactory::build($diskConfig);
        $flag = $disk->upload($file, $path);
        if ($flag == false) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, $disk->getMessage());
        }
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, $disk->getMessage(), [
            'url' => $disk->getPath()
        ]);
    }
}
