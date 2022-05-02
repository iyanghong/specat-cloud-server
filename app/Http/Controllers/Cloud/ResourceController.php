<?php

namespace App\Http\Controllers\Cloud;

use App\Core\Constructors\Model\BaseModel;
use App\Core\Enums\ErrorCode;
use App\Core\Generate\Resource\Model;
use App\Http\Controllers\Controller;

use App\Models\Cloud\Disk;
use App\Models\Member\PersonalTheme;
use App\Service\Disk\Config\DiskConfig;
use App\Service\Disk\DiskFactory;
use App\Service\Disk\Factory\DiskFactoryInterface;
use Illuminate\Http\Request;
use App\Models\Cloud\Resource;
use Illuminate\Support\Facades\DB;
use PhpParser\Builder;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 资源
 * @date : 2022/4/21 19:26
 * @author : 孤鸿渺影
 */
class ResourceController extends Controller
{

    /**
     * 上传文件
     * @date : 2022/4/21 23:25
     * @param $diskUid
     * @return string
     * @author : 孤鸿渺影
     */
    public function upload($diskUid): string
    {
        $file = request()->file('file', null);
        if ($file == null) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_NULL_ERROR, '请上传文件');
        }
        $diskModel = new Disk();
        $disk = $diskModel->findIdOrUuid($diskUid);
        if (!$disk) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '磁盘不存在');
        }

        $config = new DiskConfig();
        $config->setBucket($disk->bucket);
        $config->setBasePath($disk->base_path);
        $config->setNode($disk->node);
        $config->setAccessKeyId(maskCrypt()->decrypt($disk->access_key_id));
        $config->setAccessKeySecret(maskCrypt()->decrypt($disk->access_key_secret));
        $config->setMaxSize($disk->max_size);

        $diyPath = request()->input('path', '');
        $disk = DiskFactory::build($disk->vendor);
        $flag = $disk->upload($file, $diyPath);
        if ($flag == false) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, $disk->getMessage());
        }
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, $disk->getMessage(), [
            'url' => $disk->getPath()
        ]);
    }

    /**
     * 根据资源获取资源列表
     * @date : 2022/4/27 11:03
     * @param  $diskUid
     * @param  $resourceUid
     * @return string
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function getDiskResourcesByResource($diskUid, $resourceUid): string
    {
        $diskModel = new Disk();
        /** @var  $disk \Illuminate\Database\Eloquent\Builder */
        $disk = $diskModel->where([
            'uuid' => $diskUid,
            'user_uuid' => onlineMember()->getUuid()
        ])->first();
        if (!$disk) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '磁盘不存在');
        }
        $resourceModel = new Resource();
        //解析根访问路径
        $accessPath = $disk->access_path == -1 ? systemConfig()->get('Cloud.defaultDiskAccessPath') : $disk->access_path;
        $accessPath = rtrim($accessPath, '/') . '/' . trim($disk->base_path, '/');
        $accessPath = rtrim($accessPath, '/');
        $location = [];
        //若不为磁盘根目录
        if ($resourceUid != -1) {
            $currentResource = $resourceModel->where([
                'disk_uuid' => $disk->uuid,
                'uuid' => $resourceUid
            ])->first();

            if (!$currentResource) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '资源不存在');
            }
            $location = $currentResource->getLocation();
            $accessPath = $accessPath . '/' . $currentResource->getResourceDirectory();
        }

        array_unshift($location, [
            'disk_uuid' => $disk->uuid,
            'name' => $disk->name,
            'resource_uuid' => -1
        ]);

        $resources = $resourceModel->where([
            'disk_uuid' => $disk->uuid,
            'parent' => $resourceUid
        ])->get(['id', 'uuid', 'parent', 'disk_uuid', 'name', 'type', 'file_type', 'file_extension', 'size', 'cover', 'user_uuid', 'created_at']);


        foreach ($resources as $resource) {
            if ($resource->type === 'file') {
                $resource['path'] = $accessPath . '/' . $resource->getResourceDirectory() . $resource->name . '.' . $resource->file_extension;
            }
        }

        return api_response_show([
            'location' => $location,
            'list' => $resources
        ]);
    }

    /**
     * 获取桌面资源
     * @date : 2022/4/30 0:02
     * @return string
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function getDesktopResources()
    {
        $diskModel = new Disk();
        /** @var  $disk \Illuminate\Database\Eloquent\Builder */
        $disk = $diskModel->where([
            'is_default' => 1,
            'user_uuid' => onlineMember()->getUuid()
        ])->first();
        if (!$disk) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '桌面资源不存在');
        }
        $resourceModel = new Resource();
        $desktop = $resourceModel->where([
            'disk_uuid' => $disk->uuid,
            'type' => 'desktop'
        ])->first();
        if (!$desktop) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '桌面资源不存在');
        }
        $accessPath = $disk->access_path == -1 ? systemConfig()->get('Cloud.defaultDiskAccessPath') : $disk->access_path;
        $accessPath = rtrim($accessPath, '/') . '/' . trim($disk->base_path, '/');
        $list = $this->getResourceListUitl($desktop->uuid, $accessPath);
        $desktop->list = $list;
        $desktop->personal_setting = $this->getPersonalTheme();
        return api_response_show($desktop);
    }

    private function getPersonalTheme()
    {
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
        return $setting;
    }

    /**
     * 上传文件
     * @date : 2022/4/25 19:51
     * @param $resourceUid
     * @param string $diskUid
     * @return string
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function uploadFile($resourceUid, $diskUid = ''): string
    {

        $file = request()->file('file', null);
        if ($file == null) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_NULL_ERROR, '请上传文件');
        }
        $resourceModel = new Resource();
        $diskModel = new Disk();
        if ($resourceUid == -1 && !empty($diskUid)) {
            $disk = $diskModel->findIdOrUuid($diskUid);
            if (!$disk) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '磁盘不存在');
            }

        } else {
            $resource = $resourceModel->findIdOrUuid($resourceUid);
            if (!$resource) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '资源不存在');
            }

            $disk = $diskModel->findIdOrUuid($resource->disk_uuid);
            if (!$disk) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '磁盘不存在');
            }
        }
        if (!empty($resource)) {
            $parentsUid = $resource->parent_all ? rtrim($resource->parent_all, ",") : '';
        }
        $parent = [];
        if (!empty($parentsUid)) {
            /** @var  $resourceModel \Illuminate\Database\Query\Builder */
            $parentList = $resourceModel->whereIn('uuid', explode(',', $parentsUid))->get();
            foreach ($parentList as $item) {
                $parent[] = $item->name;
            }
        }
        $fileName = \request('name', $file->getFilename());
        $fileName = explode('.', $fileName)[0];
        $fileExtension = $file->getClientOriginalExtension();

        !empty($resource) && $parent[] = $resource->name;
        $parent[] = $fileName . "." . $fileExtension;
        $path = trim($disk->base_path, '/') . '/' . implode('/', $parent);

        DB::beginTransaction();
        $resourceFlag = false;

        if (empty($resource)) {
            $repeatNameResource = $resourceModel->where(['parent' => -1, 'disk_uuid' => $diskUid, 'name' => $fileName, 'file_extension' => $fileExtension])->first();
        } else {
            $repeatNameResource = $resource->getRepeatNameResource($fileName, $fileExtension);
        }
        if ($repeatNameResource) {
            /** @var  $resourceFlag \Illuminate\Database\Query\Builder */
            $repeatNameResource->size = $file->getSize();
            $resourceFlag = $repeatNameResource->save();
        } else {
            $data = [
                'uuid' => getUuid(),
                'disk_uuid' => $disk->uuid,
                'name' => $fileName,
                'parent' => empty($resource) ? -1 : $resource->uuid,
                'size' => $file->getSize(),
                'type' => 'file',
                'parent_all' => empty($resource) ? '' : ($resource->parent_all . $resource->uuid . ","),
                'file_type' => DiskFactory::resolveFileType($fileExtension),
                'file_extension' => $fileExtension,
                'user_uuid' => onlineMember()->getUuid(),
                'create_user' => onlineMember()->getUuid()
            ];
            $resourceFlag = $resourceModel->create($data);
        }

        if (!$resourceFlag) {
            DB::rollBack();
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '上传失败');
        }
        $diskConfig = new DiskConfig($disk->toArray());
        $diskDriver = DiskFactory::build($diskConfig);
        $flag = $diskDriver->upload($file, $path);
        if ($flag == false) {
            DB::rollBack();
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, $diskDriver->getMessage());
        }
        DB::commit();
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, $diskDriver->getMessage(), [
            'url' => $diskDriver->getPath()
        ]);


    }

    /**
     * 获取资源列表
     * @date : 2022/4/25 17:55
     * @param $resourceUid
     * @return string
     * @author : 孤鸿渺影
     */
    public function getResourceList($resourceUid): string
    {
        $resourceModel = new Resource();
        $resource = $resourceModel->findIdOrUuid($resourceUid);
        if (!$resource) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '资源不存在');
        }
        $list = $this->getResourceListUitl($resource->uuid);
        return api_response_show($list);
    }

    /**
     *
     * @date : 2022/4/25 17:54
     * @param $resourceUid
     * @return mixed
     * @author : 孤鸿渺影
     */
    private function getResourceListUitl($resourceUid, $accessPath = '')
    {
        if ($accessPath == -1) $accessPath = systemConfig()->get('Cloud.defaultDiskAccessPath');
        $resourceModel = new Resource();
        $list = $resourceModel->where([
            'parent' => $resourceUid
        ])->get();
        foreach ($list as $key => $item) {
            $list[$key]['path'] = rtrim($accessPath, '/') . '/' . $item->getResourceDirectory();
        }
        return $list;
    }


    /**
     * 列表
     * @Author:System Generate
     * @Date:2022-04-21 19:25:22
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new Resource())->index();
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2022-04-21 19:25:22
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        return resourceConstructor(new Resource())->store($request);
    }

    /**
     * 创建文件夹
     * @date : 2022/4/27 20:43
     * @param $resourceUuid
     * @param string $diskUuid
     * @return string
     * @author : 孤鸿渺影
     */
    public function createDirectory($resourceUuid, $diskUuid = ''): string
    {
        $name = \request()->input('name');
        if (empty($name)) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '目录不可为空');
        }
        $resourceModel = new Resource();
        //若为磁盘根目录
        if ($resourceUuid == -1 && !empty($diskUuid)) {
            $data = $this->createDirectoryInDiskBase($diskUuid, $name);
            if (!$data) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '目录名存在');
            }
        } else {
            $currentResource = $resourceModel->findIdOrUuid($resourceUuid);
            if (!$currentResource) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '资源不存在');
            }


            /** @var  $currentResource Resource */
            $repeatResource = $currentResource->getRepeatNameResource($name, '', false);
            if ($repeatResource) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '目录名存在');
            }
            $data = [
                'uuid' => getUuid(),
                'name' => $name,
                'disk_uuid' => $currentResource->disk_uuid,
                'parent' => $currentResource->uuid,
                'parent_all' => $currentResource->parent_all . $currentResource->uuid . ',',
            ];
        }
        $data['user_uuid'] = onlineMember()->getUuid();
        $data['create_user'] = onlineMember()->getUuid();
        $resource = $resourceModel->create($data);
        if ($resource) {
            $resource = $resource->refresh();
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '创建成功', $resource->toArray());
        }
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '创建失败');
    }

    /**
     * 在磁盘根目录创建目录
     * @date : 2022/4/27 20:44
     * @param $diskUid
     * @param $name
     * @return array|string
     * @author : 孤鸿渺影
     */
    public function createDirectoryInDiskBase($diskUid, $name): array|string
    {
        $diskModel = new Disk();
        $disk = $diskModel->findIdOrUuid($diskUid);
        if (!$disk) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '资源不存在');
        }
        $resourceModel = new Resource();
        $repeatResource = $resourceModel->where([
            'name' => $name,
            'disk_uuid' => $disk->uuid,
            'parent' => -1
        ])->first();
        if ($repeatResource) {
            return false;
        }
        return [
            'uuid' => getUuid(),
            'name' => $name,
            'disk_uuid' => $disk->uuid,
            'parent' => -1
        ];
    }


    /**
     * 重命名资源
     * @date : 2022/4/30 0:03
     * @param $resourceUid
     * @return string
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function uploadResourceName($resourceUid)
    {
        $name = \request()->input('name');
        if (empty($name)) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '名称不可为空');
        }
        $resourceModel = new Resource();
        $resource = $resourceModel->findIdOrUuid($resourceUid);
        if (!$resource) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '资源不存在');
        }
        if ($resource->name === $name) {
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '未修改');
        }
        if ($resource->type === 'desktop') {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '桌面文件夹无法修改');
        }

        /** @var  $resource Resource */
        $repeatResource = $resource->getCurrentDirectoryRepeatNameResource($name, '', $resource->type === 'file');
        if ($repeatResource) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '名称重复');
        }
        DB::beginTransaction();
        if ($resource->type === 'file') {
            $oldPath = $resource->getResourceDirectory();
        }
        $resource->name = $name;
        $flag = $resource->save();
        if ($flag) {
            if ($resource->type === 'file') {
                $resource = $resource->refresh();
                $diskModel = new Disk();
                $disk = $diskModel->findIdOrUuid($resource->disk_uuid);

                $newPath = $resource->getResourceDirectory();
                $diskConfig = new DiskConfig($disk->toArray());
                $diskDriver = DiskFactory::build($diskConfig);
                $oldPath = trim($diskConfig->getBasePath(), '/') . '/' . $oldPath;
                $newPath = trim($diskConfig->getBasePath(), '/') . '/' . $newPath;
                $flag = $diskDriver->move($oldPath, $newPath);
                if ($flag == false) {
                    DB::rollBack();
                    return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, $diskDriver->getMessage());
                }
            }
            DB::commit();
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '重命名成功');
        }
        DB::rollBack();
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '重命名失败');
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2022-04-21 19:25:22
     * @param Request $request
     * @return string
     */
    public function get(Request $request): string
    {
        return resourceConstructor(new Resource())->get($request);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2022-04-21 19:25:22
     * @param $resourceUid
     * @return string
     */
    public function show($resourceUid): string
    {
        $resourceModel = new Resource();
        $currentResource = $resourceModel->findIdOrUuid($resourceUid);
        if (!$currentResource) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '资源不存在');
        }
        $disk = (new Disk())->findIdOrUuid($currentResource->disk_uuid);
        if (!$disk) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '磁盘不存在');
        }
        if ($currentResource != 'file') {
            $likeValue = $currentResource->parent_all . $currentResource->uuid;
            $list = $resourceModel->where('parent_all', 'like', $likeValue . "%")->get();
//            $currentResource->children = $list;
            $fileTotal = 0;
            $directoryTotal = 0;
            $sizeTotal = 0;
            foreach ($list as $key => $item) {
                if ($item->type == 'file') {
                    $fileTotal++;
                    $sizeTotal += $item->size;
                } else {
                    $directoryTotal++;
                }
            }
            $currentResource->file_total = $fileTotal;
            $currentResource->directory_total = $directoryTotal;
            $currentResource->size_total = $sizeTotal;
        }
        $location = $currentResource->getLocation();

        array_unshift($location, [
            'disk_uuid' => $disk->uuid,
            'name' => $disk->name,
            'resource_uuid' => -1
        ]);

        $currentResource->location = $location;

        return api_response_show($currentResource);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2022-04-21 19:25:22
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        return resourceConstructor(new Resource())->update($request, $id);
    }

    /**
     * 删除
     * @Author:System Generate
     * @Date:2022-04-21 19:25:22
     * @param $id
     * @return string
     * @throws InvalidArgumentException
     */
    public function destroy($id): string
    {
        $resourceModel = new Resource();
        $resource = $resourceModel->findIdOrUuid($id);
        if (!$resource) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '资源不存在');
        }
        DB::beginTransaction();
        $diskModel = new Disk();
        $disk = $diskModel->findIdOrUuid($resource->disk_uuid);
        if (!$disk) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '磁盘不存在');
        }
        $diskConfig = new DiskConfig($disk->toArray());

        $basePath = trim($disk->base_path, '/');
        $diskDriver = DiskFactory::build($diskConfig);
        if ($resource->type === 'file') {
            $this->deleteResource($diskDriver, $basePath, $resource);
        } else if ($resource->type === 'desktop') {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '桌面无法删除');
        } else {
            $list = $resourceModel->where([
                ['parent_all', 'like', $resource->parent_all . $resource->uuid . '%'],
            ])->get();

            //分开两次遍历防止因为过早删除导致父找不到
            foreach ($list as $item) {
                if ($item->type === 'file') {
                    $this->deleteResource($diskDriver, $basePath, $item);
                }
            }
            foreach ($list as $item) {
                if ($item->type === 'directory') {
                    $item->delete();
                }
            }

        }
        $flag = $resource->delete();
        if ($flag) {
            DB::commit();
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '删除成功');
        }
        DB::rollBack();
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '删除失败');;
    }

    /**
     *
     * @date : 2022/4/26 23:18
     * @param DiskFactoryInterface $diskDriver
     * @param $basePath
     * @param $resource
     * @author : 孤鸿渺影
     */
    private function deleteResource(DiskFactoryInterface $diskDriver, $basePath, $resource)
    {
        $path = trim($basePath, '/') . '/' . $resource->getResourceDirectory();
        $diskDriver->delete(trim($path, '/'));
    }


    public function copyResource($currentUid, $targetDisk, $targetUid)
    {
        $resourceModel = new Resource();
        $targetResource = $resourceModel->findIdOrUuid($targetUid);
        if (!$targetResource) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '目标资源不存在');
        }
        $diskModel = new Disk();
        $disk = $diskModel->findIdOrUuid($targetResource->disk_uuid);
        if (!$disk) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '磁盘不存在');
        }
        $currentResource = $resourceModel->where(['uuid' => $currentUid])->with('children')->first();
        if (!$currentResource) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '当前资源不存在');
        }

        DB::beginTransaction();
        $resourceName = $currentResource->name;
        $index = 0;
        $repeatResource = null;
        do {
            if ($index > 0) {
                $resourceName = "${resourceName}_副本";
            }
            $index++;
            $repeatResource = $targetResource->getRepeatNameResource($resourceName, $currentResource->file_extension, $currentResource->type == 'file');
        } while ($repeatResource);
        $diskConfig = new DiskConfig($disk);
        $basePath = rtrim($diskConfig->getBasePath(), '/') . '/' . $targetResource->parent_all ? $targetResource->getResourceDirectory() : $targetResource->name;

        $this->doCopyRecursive($currentResource, $targetResource, $diskConfig, $basePath, $resourceName);
        DB::commit();
        return api_response_action(true);
    }

    /**
     *
     * @date : 2022/5/2 20:35
     * @param $resource Model 需要复制的资源
     * @param $targetResource Model 复制到xx的资源
     * @param $diskConfig
     * @param string $basePath
     * @param string $resourceName
     * @author : 孤鸿渺影
     */
    private function doCopyRecursive($resource, $targetResource, $diskConfig, $basePath = '', $resourceName = '')
    {
        $data = [
            'uuid' => getUuid(),
            'user_uuid' => onlineMember()->getUuid(),
            'disk_uuid' => $targetResource['disk_uuid'],
            'create_user' => onlineMember()->getUuid(),
            'name' => $resourceName ? $resourceName : $resource['name'],
            'size' => $resource['size'],
            'file_extension' => $resource['file_extension'],
            'cover' => $resource['cover'],
            'parent' => $targetResource['uuid'],
            'parent_all' => $targetResource->parent_all . $targetResource->uuid . ",",
            'type' => $resource['type']
        ];
        if ($resource->type === 'file') {
            $driver = DiskFactory::build($diskConfig);
            $oldPath = rtrim($diskConfig->getBasePath(), '/') . '/' . $resource->getResourceDirectory();
            $newPath = $basePath . '/' . $data['name'] . "." . $data['file_extension'];
            $driver->copy($oldPath, $newPath);
            (new Resource())->create($data);
        } else {

            $createResource = (new Resource())->create($data);
            if ($createResource && !empty($resource->children)) {
                $createResource->refresh();
                foreach ($resource->children as $item) {
                    $this->doCopyRecursive($item, $createResource, $diskConfig, $basePath . "/" . $createResource->name);
                }
            }
        }

    }
}
