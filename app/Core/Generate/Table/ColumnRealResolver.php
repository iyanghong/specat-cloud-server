<?php


namespace App\Core\Generate\Table;


class ColumnRealResolver
{
    private Column $column;


    private string $real = '';
    private Option $option;


    private array $defaultRealResolvers = [
        'createTimeField',
        'updateTimeField',
        'deleteTimeField',
        'createUserField',
        'updateUserField',
        'deleteUserField',
        'ip',
        'uuid'
    ];

    public function __construct(Column $column, Option $option)
    {
        $this->column = $column;
        $this->option = $option;
    }

    /**
     * @return bool
     * @throws \ErrorException
     */
    public function handle(): bool
    {
        foreach ($this->defaultRealResolvers as $resolver) {
            $methodName = 'check' . ucfirst($resolver);
            if (method_exists($this, $methodName)) {
                $flag = $this->$methodName();
                if ($flag === true) {
                    return true;
                }
            } else {
                throw new \ErrorException(sprintf('列解析错误：未适配[%s]解析器', $resolver));
            }
        }
        return false;
    }

    public function checkUuid(): bool
    {
        if (stripos($this->column->getName(), 'uuid') !== false) {
            similar_text($this->column->getName(), $this->option->getTableName() . '_uuid', $percent);
            if ($percent > 50 || $this->column->getName() === 'uuid') {
                $this->real = 'uuid';
                return true;
            }
        }
        return false;
    }

    private function checkCreateTimeField(): bool
    {
        if ($this->column->getName() === $this->option->getCreateTimeField()) {
            $this->real = 'createTimeField';
            return true;
        }
        return false;
    }

    private function checkUpdateTimeField(): bool
    {
        if ($this->column->getName() === $this->option->getUpdateTimeField()) {
            $this->real = 'updateTimeField';
            return true;
        }
        return false;
    }

    private function checkDeleteTimeField(): bool
    {
        if ($this->column->getName() === $this->option->getDeleteTimeField()) {
            $this->real = 'deleteTimeField';
            return true;
        }
        return false;
    }

    private function checkCreateUserField(): bool
    {
        if ($this->column->getName() === $this->option->getCreateUserField()) {
            $this->real = 'createUserField';
            return true;
        }
        return false;
    }

    private function checkUpdateUserField(): bool
    {
        if ($this->column->getName() === $this->option->getUpdateUserField()) {
            $this->real = 'updateUserField';
            return true;
        }
        return false;
    }

    private function checkDeleteUserField(): bool
    {
        if ($this->column->getName() === $this->option->getDeleteUserField()) {
            $this->real = 'deleteUserField';
            return true;
        }
        return false;
    }

    private function checkIp(): bool
    {
        if (stripos($this->column->getName(), 'ip') !== false) {
            $this->real = 'ip';
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getReal(): string
    {
        return $this->real;
    }


}