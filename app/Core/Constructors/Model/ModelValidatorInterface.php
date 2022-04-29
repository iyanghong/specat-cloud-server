<?php


namespace App\Core\Constructors\Model;


interface ModelValidatorInterface
{

    public function validateCreate(): ModelValidatorInterface;
    public function validateUpdate($id = null): ModelValidatorInterface;
    /**
     * @return bool
     */
    public function isSuccess(): bool;
    /**
     * @return string
     */
    public function getMessage(): string;
    /**
     * @return array
     */
    public function getData(): array;
}