<?php


namespace App\Service\OnlineMember;


use App\Exceptions\NoLoginException;
use Illuminate\Database\Eloquent\Model;

interface OnlineMemberInterface
{
    /**
     * 登录
     * @param $token
     * @param $user
     * @param null $oldToken
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function online(string $token, Model $user, $oldToken = null):array ;

    /**
     * 获取 bearer 验证token
     * @return string
     */
    public function getBearerAuth():string;

    public function logout() : void;
    public function isLogin() : bool;

    /**
     * 拦截未登录
     * Date : 2021/4/19 22:38
     * Author : 孤鸿渺影
     * @return bool
     * @throws NoLoginException
     */
    public function loginIntercept();
    /**
     * 获取用户Model
     * @param array $attribute
     * Date : 2021/4/19 17:05
     * Author : 孤鸿渺影
     * @return Model
     */
    public function getUserModel(array $attribute = []) : Model;


    public function refresh();
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getUuid();

    /**
     * @return string
     */
    public function getToken();

    public function getEmail(): string;
    public function getPhone(): string;
    /**
     * @return bool
     */
    public function isSupperAdmin();

    /**
     * @return mixed
     */
    public function getUser();

    /**
     * @return array
     */
    public function getRoles(): array;

    /**
     * @return array
     */
    public function getRoleName(): array;

}