<?php


namespace App\Core\Generate\Table;

class Option
{
    /**
     * @var string 表名
     */
    private string $tableName = '';
    /**
     * @var string 主键名
     */
    private string $primaryKey = '';
    /**
     * @var array 外键列表
     */
    private array $foreignKey = [];

    /**
     * @var bool 是否允许自动解析uuid
     */
    private bool $isAutoResolveUuid = true;

    /**
     * @var bool 是否自动解析real
     */
    private bool $isAutoResolve = true;

    private string $createTimeField = 'created_at';
    private string $updateTimeField = 'updated_at';
    private string $deleteTimeField = 'deleted_at';
    private string $createUserField = 'create_user';
    private string $updateUserField = 'update_user';
    private string $deleteUserField = 'delete_user';


    /**
     * 允许自动填充数据的填充器
     * @var array
     */
    private array $allowApplier = [
        'createTimeField',
        'updateTimeField',
        'deleteTimeField',
        'createUserField',
        'updateUserField',
        'deleteUserField',
        'uuid',
        'ip'
    ];

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     */
    public function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return array
     */
    public function getForeignKey(): array
    {
        return $this->foreignKey;
    }

    /**
     * @param array $foreignKey
     */
    public function setForeignKey(array $foreignKey): void
    {
        $this->foreignKey = $foreignKey;
    }

    /**
     * @return bool
     */
    public function isAutoResolveUuid(): bool
    {
        return $this->isAutoResolveUuid;
    }

    /**
     * @param bool $isAutoResolveUuid
     */
    public function setIsAutoResolveUuid(bool $isAutoResolveUuid): void
    {
        $this->isAutoResolveUuid = $isAutoResolveUuid;
    }

    /**
     * @return bool
     */
    public function isAutoResolve(): bool
    {
        return $this->isAutoResolve;
    }

    /**
     * @param bool $isAutoResolve
     */
    public function setIsAutoResolve(bool $isAutoResolve): void
    {
        $this->isAutoResolve = $isAutoResolve;
    }




    /**
     * @return string
     */
    public function getCreateTimeField(): string
    {
        return $this->createTimeField;
    }

    /**
     * @param string $createTimeField
     */
    public function setCreateTimeField(string $createTimeField): void
    {
        $this->createTimeField = $createTimeField;
    }

    /**
     * @return string
     */
    public function getUpdateTimeField(): string
    {
        return $this->updateTimeField;
    }

    /**
     * @param string $updateTimeField
     */
    public function setUpdateTimeField(string $updateTimeField): void
    {
        $this->updateTimeField = $updateTimeField;
    }

    /**
     * @return string
     */
    public function getDeleteTimeField(): string
    {
        return $this->deleteTimeField;
    }

    /**
     * @param string $deleteTimeField
     */
    public function setDeleteTimeField(string $deleteTimeField): void
    {
        $this->deleteTimeField = $deleteTimeField;
    }

    /**
     * @return string
     */
    public function getCreateUserField(): string
    {
        return $this->createUserField;
    }

    /**
     * @param string $createUserField
     */
    public function setCreateUserField(string $createUserField): void
    {
        $this->createUserField = $createUserField;
    }

    /**
     * @return string
     */
    public function getUpdateUserField(): string
    {
        return $this->updateUserField;
    }

    /**
     * @param string $updateUserField
     */
    public function setUpdateUserField(string $updateUserField): void
    {
        $this->updateUserField = $updateUserField;
    }

    /**
     * @return string
     */
    public function getDeleteUserField(): string
    {
        return $this->deleteUserField;
    }

    /**
     * @param string $deleteUserField
     */
    public function setDeleteUserField(string $deleteUserField): void
    {
        $this->deleteUserField = $deleteUserField;
    }

    /**
     * @return array
     */
    public function getAllowApplier(): array
    {
        return $this->allowApplier;
    }

    /**
     * @param array $allowApplier
     */
    public function setAllowApplier(array $allowApplier): void
    {
        $this->allowApplier = $allowApplier;
    }





}