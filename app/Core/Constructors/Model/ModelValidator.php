<?php


namespace App\Core\Constructors\Model;


use App\Core\Constructors\Controller\DataHandler;
use App\Core\Enums\ErrorCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ModelValidator implements ModelValidatorInterface
{

    private bool $isSuccess = false;
    private string $message = '';
    private array $data = [];
    private BaseModel $model;

    private Request $request;

    public function __construct(BaseModel $model,?Request $request = null)
    {
        $this->model = $model;
        $this->request = isset($request) ? $request : request();
    }

    public function validateCreate(?array $data = null): ModelValidatorInterface
    {
        $field = $this->model->getFillable();
        if($data === null) {
            $data = $this->request->only($field);
        }
        // 自动填充数据
        $autoFillList = $this->model->getAutoFill();
        if (!empty($autoFillList)) {
            $dataHandler = new DataHandler();
            foreach ($autoFillList as $key => $mode) {
                $data[$key] = $dataHandler->fill($mode);
            }
        }

        //开始验证数据
        $rule = $this->model->getRule();
        if (!empty($rule)) {
            //解析验证规则
            foreach ($rule as $key => $value) {
                //解析exists验证
                $rule[$key] = str_replace('exists:', 'exists:' . $this->model->getConnectionName() . '.', $rule[$key]);

                $rule[$key] = explode('|', $rule[$key]);

                $index = array_search('unique', $rule[$key]);

                if ($index >= -1) {
                    $table = $this->model->getTable();
                    if($this->model->getConnectionName()){
                        $table = $this->model->getConnectionName() . "." . $table;
                    }
                    $rule[$key][$index] = Rule::unique($table);
//                        if (in_array('user_id', $field)) {
//                            $rule[$key][$index] = $rule[$key][$index]->ignore(getNowUserId(), 'user_id');
//                        }
                }
            }
        }
        //开始验证
        $validate = \Validator::make($data, $rule, $this->model->getMessage(), $this->model->getAttributeAliasList());
        if ($validate->fails()) {
            $this->message = $validate->errors()->first();
            $this->data = $data;
            return $this;
        }

        //开始自动处理字段
        $autoHandleList = $this->model->getAutoHandle();
        if (!empty($autoHandleList)) {
            $dataHandler = new DataHandler();
            foreach ($autoHandleList as $key => $mode) {
                isset($data[$key]) && $data[$key] = $dataHandler->processing($data[$key], $mode);
            }
        }
        $this->data = $data;
        $this->isSuccess = true;
        return $this;
    }

    public function validateUpdate($id = null,?array $data = null): ModelValidatorInterface
    {
        $primaryKey = $this->model->getKeyName();
        $field = $this->model->getFillable();
        if($data === null) {
            $data = $this->request->only($field);
        }
        if (empty($data)) {
            //参数不可为空
            $this->message = ErrorCode::getMessage(ErrorCode::$ENUM_PARAM_NULL_ERROR);
            $this->isSuccess = false;
            return $this;
        }

        //验证规则
        $rule = $this->model->getRule();
        foreach ($rule as $key => $value) {
            if (!isset($data[$key])) {
                unset($rule[$key]);
            } else {
                $rule[$key] = str_replace('exists:', 'exists:' . $this->model->getConnectionName() . '.', $rule[$key]);
                $rule[$key] = explode('|', $rule[$key]);

                //验证唯一值，忽略当前行记录
                $index = array_search('unique', $rule[$key]);
                if ($index >= -1) {

                    $table = $this->model->getTable();
                    if($this->model->getConnectionName()){
                        $table = $this->model->getConnectionName() . "." . $table;
                    }

                    $rule[$key][$index] = Rule::unique($table,$key)->ignore($id, $primaryKey);
                }
            }
        }


        //开始验证
        if (!empty($rule)) {
            $validate = Validator::make($data, $rule, $this->model->getMessage(), $this->model->getAttributeAliasList());
            if ($validate->fails()) {
                $this->message = $validate->errors()->first();
                $this->isSuccess = false;
                $this->data = $data;
                return $this;
            }
        }
        $this->data = $data;
        $this->isSuccess = true;
        return $this;
    }


    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }


}