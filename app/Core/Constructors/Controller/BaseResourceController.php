<?php


namespace App\Core\Constructors\Controller;


use App\Core\Constructors\Model\BaseModel;
use App\Core\Enums\ErrorCode;
use App\Exceptions\NoLoginException;
use App\Jobs\ImportExcelJob;
use App\Models\System\SystemTask;
use App\QuickBill\Importer\UserImport;
use App\Service\Disk\UploadException;
use ErrorException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

class BaseResourceController implements BaseResourceControllerInterface
{
    protected BaseModel $model;

    private bool $isCache = false;

    private string $cacheKeySuffix = '';

    public function __construct(BaseModel $model, string $cacheKeySuffix = '')
    {
        $this->model = $model;
        if (!empty($cacheKeySuffix)) {
            $this->isCache = true;
            $this->cacheKeySuffix = $cacheKeySuffix;
        }
    }

    /**
     * @throws ErrorException
     */
    private function filterConstructor($filter = [], array $option = []): Builder
    {
        $filterConstructor = new FilterConstructor($this->model, \request(), $option);
        return $filterConstructor->filter($filter);
    }

    /**
     * 显示列表
     * [GET] /model
     * @return string
     */
    public function index(): string
    {
        // TODO: Implement index() method.
        $paging = true;
        if (request()->exists('paging')) {
            $p = request()->input('paging');
            $paging = !($p === 'false') && (bool)$p;
        }

        if ($paging === true) {
            return $this->listPaging();
        }
        return $this->listNoPaging();
    }

    private function listPaging()
    {
        $pageSize = 10;
        $request = \request();
        if ($request->exists('pageSize')) {
            $pageSize = $request->input('pageSize');
        } elseif ($request->exists('limit')) {
            $pageSize = $request->input('limit');
        }
        if ($this->isCache) {
            $page = $request->input('page') ?? 1;
            $filter = '_';
            $temp = $request->only($this->model->getAllField());
            !empty($temp) && $filter .= json_encode($temp);
            if ($request->exists('where')) {
                $filter .= "_" . $request->input('where');
            }
            $key = "{$this->cacheKeySuffix}_list_{$filter}_{$pageSize}_{$page}";
            return Cache::tags([$this->cacheKeySuffix, "{$this->cacheKeySuffix}_list"])->remember($key, CACHE_TIME, function () use ($pageSize) {
                return api_response_list($this->filterConstructor()->paginate($pageSize));
            });
        }
        return api_response_list($this->filterConstructor()->paginate($pageSize));
    }

    private function listNoPaging()
    {
        if ($this->isCache) {
            return Cache::tags([$this->cacheKeySuffix, "{$this->cacheKeySuffix}_list"])->remember("{$this->cacheKeySuffix}_all", CACHE_TIME, function () {
                return api_response_show($this->filterConstructor()->get());
            });
        }
        return api_response_show($this->filterConstructor()->get());
    }

    /**
     * 创建model的页面
     * @return string
     * @throws Throwable
     */
    public function create(): string
    {
        return '';
    }

    /**
     * 编辑model详细信息
     * [GET] /model/{id}/model
     * @param int $id
     * @return string
     */
    public function edit($id): string
    {
        // TODO: Implement edit() method.
        $entity = $this->model->find($id);
        $message = '';
        if (!isset($entity)) {
            $tableInfo = $this->model->getTableInfo();
            if (!empty($tableInfo)) {
                $message = sprintf("无该`%s`信息", str_replace('表', '', $tableInfo));
            }
        }
        return api_response_show($entity, null, $message);
    }

    /**
     * 显示model信息
     * @param int $id
     * @param array $option
     * Date : 2021/4/20 21:27
     * Author : 孤鸿渺影
     * @return string
     */
    public function show($id, array $option = []): string
    {
        // TODO: Implement show() method.
        if ($this->isCache) {
            return Cache::tags($this->cacheKeySuffix)->remember("{$this->cacheKeySuffix}:{$id}", CACHE_TIME, function () use ($id) {
                return api_response_show($this->model->findIdOrUuid($id));
            });
        }
        return api_response_show($this->model->findIdOrUuid($id));
    }

    /**
     *
     * @param Request $request
     * @param array $option
     * Date : 2021/4/20 21:27
     * Author : 孤鸿渺影
     * @return string
     * @throws ErrorException
     */
    public function get(Request $request, array $option = []): string
    {
        // TODO: Implement get() method.
        $filterConstructor = new FilterConstructor($this->model, \request(), $option);
        $model = $filterConstructor->filter();
        if (empty($filterConstructor->getFilterString())) {
            return api_response_show(false, ErrorCode::$ENUM_PARAM_NULL_ERROR);
        }
        if ($this->isCache) {
            $filter = '_';
            $temp = $request->only($this->model->getAllField());
            !empty($temp) && $filter .= json_encode($temp);
            if ($request->exists('where')) {
                $filter .= "_" . $request->input('where');
            }
            $key = "{$this->cacheKeySuffix}_get_{$filter}";
            return Cache::tags([$this->cacheKeySuffix, "{$this->cacheKeySuffix}_get"])->remember($key, CACHE_TIME, function () use ($model) {
                return api_response_show($model->first());
            });
        }

        return api_response_show($model->first());
    }

    /**
     * 创建model
     * url : [POST] /model
     * @param Request $request
     * @return string
     * @throws Throwable
     */
    public function store(Request $request): string
    {
        // TODO: Implement store() method.
        DB::beginTransaction(); //事务开启
        try {
            $validator = $this->model->createValidate();
            if (!$validator->isSuccess()) {
                return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->getMessage());
            }
            $data = $validator->getData();
            //写入数据
            $entity = $this->model->create($data);

            if ($entity) {
                DB::commit();//数据提交
                $responseData = [];
                if (!empty($this->model->getKeyName())) {
                    $responseData[$this->model->getKeyName()] = $entity->getKey();
                }
                !empty($this->cacheKeySuffix) && $this->clear(); //清楚缓存
                return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '', $responseData);
            }


            DB::rollBack();
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR);
        } catch (ErrorException $exception) {
            DB::rollBack();
            return api_response_exception($exception);
        }
    }

    /**
     * 删除model
     * [DELETE] /model/{id}
     * @param int $id
     * @return string
     */
    public function destroy($id): string
    {
        // TODO: Implement destroy() method.
        $entity = $this->model->find($id);
        if (empty($entity)) {
            $tableInfo = $this->model->getTableInfo();
            if (!empty($tableInfo)) {
                $message = sprintf("无该`%s`信息", str_replace('表', '', $tableInfo));
            }
            return api_response_action(false, ErrorCode::$ENUM_NO_DATA_ERROR, $message ?? '');
        }
        $flag = $entity->delete();
        if ($flag) {
            !empty($this->cacheKeySuffix) && $this->clear($id); //清楚缓存
            return api_response_action(true, 0, '删除成功');
        }
        return api_response_action(false);
    }

    /**
     * 更新model
     * [PATCH] /model/{id}
     * @param Request $request
     * @param int $id
     * @return string
     * @throws Throwable
     */
    public function update(Request $request, $id): string
    {
        // TODO: Implement update() method.
        DB::beginTransaction();
        try {
            $validator = $this->model->updateValidator($id);
            if (!$validator->isSuccess()) {
                return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->getMessage());
            }
            $data = $validator->getData();
            $entity = $this->model->find($id);
            if (is_null($entity)) {
                $tableInfo = $this->model->getTableInfo();
                if (!empty($tableInfo)) {
                    $message = sprintf("无该`%s`信息", str_replace('表', '', $tableInfo));
                }
                return api_response_action(false, ErrorCode::$ENUM_NO_DATA_ERROR, $message);
            }

            //开始自动处理字段
            $autoHandleList = $this->model->getAutoHandle();
            if (!empty($autoHandleList)) {
                $dataHandler = new DataHandler();
                foreach ($autoHandleList as $key => $mode) {
                    isset($data[$key]) && $data[$key] = $dataHandler->processing($data[$key], $mode);
                }
            }
            //开始更新
            $flag = $entity->update($data);
            if ($flag) {
                !empty($this->cacheKeySuffix) && $this->clear($id); //清楚缓存
                DB::commit();
                return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '修改成功');
            }
            DB::rollBack();
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR);
        } catch (\Exception $exception) {
            DB::rollBack();
            return api_response_exception($exception);
        }
    }

    /**
     * 批量插入
     * Date : 2021/4/20 22:51
     * Author : 孤鸿渺影
     * @return string
     * @throws Throwable
     */
    public function batchInsert(): string
    {
        $list = json_decode(request()->input('list'), true);
        if (empty($list)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_NULL_ERROR);
        }
        DB::beginTransaction();
        foreach ($list as $key => $item) {
            $validate = $this->model->createValidate($item);
            if (!$validate->isSuccess()) {
                DB::rollBack();
                return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, "第" . ($key + 1) . "个验证失败:" . $validate->getMessage());
            }

            if ($this->model->create($validate->getData())) {
                DB::rollBack();
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, "添加第" . ($key + 1) . "个失败");
            }
        }
        DB::commit();
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '添加成功');
    }


    /**
     * 导入Excel
     * Date : 2021/5/7 14:51
     * Author : 孤鸿渺影
     * @param $importer
     * @return string
     * @throws InvalidArgumentException
     */
    public function importExcelTask($importer): string
    {

        if (!onlineMember()->isLogin()) {
            return api_response_action(false, ErrorCode::$ENUM_NO_LOGIN_ERROR);
        }
        $file = request()->file('file', null);
        if ($file == null) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_NULL_ERROR, '请上传文件');
        }
        if (!$file->isValid()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_NULL_ERROR, '文件不合法');
        }
        $fileExtension = $file->getClientOriginalExtension();
        if (!in_array($fileExtension, ['xlsx', 'xls'])) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_NULL_ERROR, '请上传Excel格式');
        }

        $uuid = getUuid();
        $systemTaskModel = new SystemTask();
        $path = sprintf('upload/excel/%s.%s', $uuid, $fileExtension);
        $force = (bool)request()->input('force', true);
        $option = [
            'file_path' => $path,
            'task_uuid' => $uuid,
            'force' => $force,
            'user_uuid' => onlineMember()->getUuid(),
            'user_id' => onlineMember()->getId(),
            'importer' => $importer
        ];
        try {
            $flag = localDisk()->upload($file, $path);
            if ($flag == false) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '未知错误');
            }

            /* @var $systemTaskModel Builder */
            $systemTask = $systemTaskModel->create([
                'uuid' => $uuid,
                'title' => request()->input('title', '导入Excel数据'),
                'status' => 0,
                'belong' => 'Import Excel',
                'user_uuid' => onlineMember()->getUuid(),
                'option' => json_encode($option)
            ]);
            if (!$systemTask) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '任务请求失败');
            }
            $this->dispatch(new ImportExcelJob($uuid, UserImport::class, $option));
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS);

        } catch (UploadException $exception) {
            return api_response_exception($exception);
        }


    }

    /**
     *
     * @param array $field
     * @param array $option
     * Date : 2021/4/21 22:10
     * Author : 孤鸿渺影
     * @return string
     * @throws NoLoginException
     */
    public function listNowOnlineUser(array $field = ['uuid' => 'user_uuid'], array $option = []): string
    {
        onlineMember()->loginIntercept();
        $filter = [];
        if (isset($field['uuid'])) {
            $filter[$field['uuid']] = onlineMember()->getUuid();
        } else if (isset($field['id'])) {
            $filter[$field['id']] = onlineMember()->getId();
        }
        $paging = isset($option['paging']) && (bool)$option['paging'];
        $getColumns = isset($option['only']) && is_array($option['only']) ? $option['only'] : ['*'];
        if (!$paging) {
            $list = $this->filterConstructor($filter, $option)->get($getColumns);
            return api_response_show($list);
        }
        $pageSize = 10;
        if (request()->exists('pageSize')) {
            $pageSize = request()->input('pageSize');
        } elseif (request()->exists('limit')) {
            $pageSize = request()->input('limit');
        }
        return api_response_list($this->filterConstructor($filter, $option)->paginate($pageSize, $getColumns));


    }

    /**
     * 清理缓存
     * @param null $id
     */
    public function clear($id = null): void
    {

        if (empty($this->cacheKeySuffix)) return;
        if (is_null($id)) {
            Cache::tags($this->cacheKeySuffix)->flush();
        } else {
            Cache::tags($this->cacheKeySuffix)->forget("{$this->cacheKeySuffix}:{$id}");
            Cache::tags("{$this->cacheKeySuffix}_list")->flush();
        }
    }
}
