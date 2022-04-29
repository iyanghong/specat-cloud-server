<?php


namespace App\Core\Excel;


use App\Core\Constructors\Model\BaseModel;
use App\Core\Constructors\Model\BaseModelInterface;
use Maatwebsite\Excel\Concerns\Importable;

class Importer
{
    use Importable;

    protected $validate = true;

    protected $model = null;


    public function __construct()
    {
        if (isset($this->model) && !is_object($this->model)) {
            if (class_exists($this->model)) {
                $this->model = new $this->model();
            } else {
                $this->model = null;
            }
        }
    }

    public function formatter(array $row)
    {
        return $row;
    }

    /**
     * @return bool
     */
    public function isValidate(): bool
    {
        return $this->validate;
    }

    /**
     * @param bool $validate
     */
    public function setValidate(bool $validate): void
    {
        $this->validate = $validate;
    }

    /**
     * @return null
     */
    public function getModel(): ?BaseModel
    {
        return $this->model;
    }


}