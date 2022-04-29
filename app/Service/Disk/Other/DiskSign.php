<?php

namespace App\Service\Disk\Other;

use App\Core\Constructors\Controller\DataHandler;
use App\Core\Enums\ErrorCode;
use App\Models\Cloud\Disk;
use App\Models\Cloud\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 孤鸿渺影
 * 2022/4/21 21:29
 * DiskSign
 */
class DiskSign
{

    public function refreshCache(): void
    {


    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2022-04-21 17:30:44
     * @return string
     */
    public function customer(): string
    {
        $diskModel = new Disk();
        $validator = $diskModel->createValidate();
        if (!$validator->isSuccess()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->getMessage());
        }
        $data = $validator->getData();
        DB::beginTransaction();
        /** @var  $diskModel Builder */
        $data['is_default'] = 0;
        $data['access_key_id'] = maskCrypt()->encrypt($data['access_key_id']);
        $data['access_key_secret'] = maskCrypt()->encrypt($data['access_key_secret']);
        $entity = $diskModel->create($data);
        if ($entity) {
            DB::commit();//数据提交
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '', [
                'id' => $entity->id
            ]);
        }
        DB::rollBack();
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '新增失败');
    }

    /**
     * 领取系统默认磁盘
     * @date : 2022/4/21 19:17
     * @return string
     * @author : 孤鸿渺影
     */
    public function default(): string
    {
        $diskModel = new Disk();
        /** @var  $diskModel Builder */
        $disk = $diskModel->where([
            'is_default' => 1,
            'user_uuid' => onlineMember()->getUuid()
        ])->first();
        if ($disk) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '您已领取系统默认磁盘，无法继续领取');
        }
        $data = [];
        // 自动填充数据
        $autoFillList = $diskModel->getAutoFill();

        $dataHandler = new DataHandler();
        if (!empty($autoFillList)) {
            foreach ($autoFillList as $key => $mode) {
                $data[$key] = $dataHandler->fill($mode);
            }
        }
        $data['is_default'] = 1;
        $data['name'] = '系统默认磁盘';
        $data['access_key_id'] = -1;
        $data['access_key_secret'] = -1;
        $data['access_path'] = -1;
        $data['max_size'] = -1;
        $data['node'] = -1;
        $data['bucket'] = -1;
        $data['base_path'] = base_convert(onlineMember()->getId(), 10, 32);
        DB::beginTransaction();
        $entity = $diskModel->create($data);
        if ($entity) {
            $resource = [
                'uuid' => getUuid(),
                'disk_uuid' => $entity->uuid,
                'name' => 'desktop',
                'type' => 'desktop',
                'user_uuid' => onlineMember()->getUuid(),
                'create_user' => onlineMember()->getUuid()
            ];
            /** @var  $resourceModel Builder */
            $resourceModel = new Resource();
            $resourceFlag = $resourceModel->create($resource);
            if ($resourceFlag) {
                DB::commit();
                return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '', [
                    'id' => $entity->id
                ]);
            }
            DB::rollBack();
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '资源初始化失败');
        }
        DB::rollBack();
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '领取失败');
    }

}
