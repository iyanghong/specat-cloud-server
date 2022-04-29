<?php


namespace App\Core\Constructors\Controller;


use Illuminate\Support\Facades\Hash;

class DataHandler implements DataHandlerInterface
{

    /**
     * 填充数据
     * @param $mode --模式
     * @return false|int|string|null
     */
    public function fill($mode)
    {
        $value = '';
        switch ($mode) {
            case 'ip':
                $value = request()->ip();
                break;
            case 'date':
                $value = \date('Y-m-d');
                break;
            case 'updateTimeField':
            case 'createTimeField':
            case  'timestamp':
                $value = time();
                break;
            case 'createTimeField:datetime':
            case 'updateTimeField:datetime':
                $value = \date('Y-m-d H:i:s');
                break;
            case 'uuid':
                $value = getUuid();
                break;
            case 'updateUserField':
            case 'createUserField':
                $value = onlineMember()->getId();
                break;
            case 'createUserField:uuid':
            case 'updateUserField:uuid':
                $value = onlineMember()->getUuid();
                break;
            default:
                $value = $mode;
                break;
        }
        return $value;
    }

    /**
     * 处理数据
     * @param $value
     * @param $mode
     * @return false|int|string
     */
    public function processing($value, $mode)
    {
        switch ($mode) {
            case 'md5':
                $value = md5($value);
                break;
            case 'timestamp':
                $value = strtotime($value);
                break;
            case 'hash':
                $value = Hash::make($value);
                break;
        }
        return $value;

    }
}
