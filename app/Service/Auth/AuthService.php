<?php


namespace App\Service\Auth;


use App\Core\Enums\ErrorCode;
use App\Service\OnlineMember\OnlineMemberInterface;
use App\Service\SystemConfig\SystemConfigServiceInterface;
use Illuminate\Http\Request;

class AuthService implements AuthServiceInterface
{
    /**
     * @array 默认不需要登录白名单
     */
    private array $defaultIgnoreLogin = [];
    /**
     * @array 忽略登录白名单
     */
    private array $ignoreLogin = [];
    /**
     * @array 忽略权限白名单
     */
    private array $ignoreAuth = [];
    /**
     * @array 忽略Token白名单
     */
    private array $ignoreToken = [];
    /**
     * @array 拥有权限列表
     */
    private array $permission = [];
    /**
     * @object  用户实体
     */
    private OnlineMemberInterface $user;

    private Request $request;


    private string $message = '';
    private int $code = 0;

    public function __construct(?Request $request = null, ?OnlineMemberInterface $user = null)
    {
        $this->request = $request === null ? \request() : $request;
        $this->user = $user === null ? onlineMember() : $user;
    }


    /**
     *
     * @param string|null $key
     * @date : 2021/5/27 20:09
     * @return bool
     * @throws \App\Exceptions\NoLoginException
     * @author : 孤鸿渺影
     */
    public function check(?string $key = null): bool
    {
        // TODO: 核查权限
        //检查是否开启权限
        if (!config('system.permissions')) {
            return true;
        }
        if ($key === null) {
            $key = $this->request->route()->getName();
        }
        if ($this->checkIgnoreLogin($key) === true) {
            return true;
        }
        //验证登录
        if ($this->checkIgnoreLogin($key) === false && $this->checkLogin() === false) {
            $this->code = ErrorCode::$ENUM_NO_LOGIN_ERROR;
            $this->message = ErrorCode::getMessage($this->code);
            return false;
        }
        $roleAuthDevice = new RoleAuthDevice();
        $this->permission = $roleAuthDevice->getApiList(onlineMember()->getRoles());
        //验证权限
        if ($this->checkAuth($key) === false) {
            $this->code = ErrorCode::$ENUM_API_NO_AUTH_ERROR;
            $this->message = ErrorCode::getMessage($this->code);
            return false;
        }
        return true;
    }


    /**
     * @Notes: 检查是否为免登陆白名单
     * @Interface checkIgnoreLogin
     * @param string $key
     * @return bool
     * @Author: TS
     * @Time: 2020-06-22   20:45
     */
    private function checkIgnoreLogin(string $key): bool
    {

        //检查是否有全局忽略
        if (in_array('*', $this->ignoreLogin)) {
            return true;
        }
        foreach ($this->ignoreLogin as $value) {
            $index = strpos($value, "*");    //获取*首次出现的位置
            $tmpKey = $key;
            if ($index) {
                //截取字符串
                $value = substr($value, 0, $index);
                $tmpKey = substr($tmpKey, 0, $index);
            }
            if ($value === $tmpKey) {
                return true;
            }
        }

        return false;
    }

    /**
     * @Notes:检查是否为权限白名单
     * @Interface checkIgnoreAuth
     * @param string $key
     * @return bool
     * @Author: TS
     * @Time: 2020-06-22   20:47
     */
    private function checkAuth(string $key): bool
    {
        //合并权限白名单，与拥有权限
        $list = array_merge($this->permission, $this->ignoreAuth);
        //检查是否全局忽略
        if (in_array('*', $list)) {
            return true;
        }
        foreach ($list as $value) {
            $index = strpos($value, "*");    //获取*首次出现的位置
            $tmpKey = $key;
            if ($index) {
                //截取字符串
                $value = substr($value, 0, $index);
                $tmpKey = substr($tmpKey, 0, $index);
            }
            if ($value === $tmpKey) {
                return true;
            }
        }
        return false;
    }

    /**
     * @Notes:验证登录
     * @Interface checkLogin
     * @return bool
     * @Author: TS
     * @Time: 2020-06-22   17:45
     */
    private function checkLogin(): bool
    {
        if ($this->user->getToken() != '') {
            return true;
        }
        return false;
    }

    /**
     * @Notes:初始化
     * @Interface init
     * @param SystemConfigServiceInterface $configService
     * @Author: TS
     * @Time: 2020-06-22   21:33
     */
    public function init(?SystemConfigServiceInterface $configService = null): void
    {
        $this->ignoreLogin = config('system.permissionsIgnoreLogin') ?? ['*'];
        $this->ignoreAuth = config('system.permissionsIgnoreAuth') ?? ['*'];
    }


    /**
     * @Notes:获取角色菜单导航
     * @Interface getRoleMenu
     * @param array $roleList
     * @return array
     * @Author: TS
     * @Time: 2020-12-01   20:03
     */
    public function getRoleMenu(array $roleList): array
    {
        $roleMenu = new RoleMenuDevice();
        return $roleMenu->getUserMenu($roleList);
    }

    /**
     * @Notes:刷新菜单导航缓存
     * @Interface refreshLeftMenu
     * @Author: TS
     * @Time: 2020-12-02   18:34
     */
    public function refreshLeftMenu(): void
    {
        $roleMenu = new RoleMenuDevice();
        $roleMenu->refresh();
    }

    /**
     * @Notes:刷新角色权限缓存
     * @Interface refreshLeftAuth
     * @Author: TS
     * @Time: 2020-12-02   18:35
     */
    public function refreshLeftAuth(): void
    {
        $roleAuth = new RoleAuthDevice();
        $roleAuth->refresh();
    }


    /**
     * @return mixed
     */
    public function getDefaultIgnoreLogin(): array
    {
        return $this->defaultIgnoreLogin;
    }

    /**
     * @param mixed $defaultIgnoreLogin
     */
    public function setDefaultIgnoreLogin($defaultIgnoreLogin): void
    {
        $this->defaultIgnoreLogin = $defaultIgnoreLogin;
    }

    /**
     * @return mixed
     */
    public function getIgnoreLogin(): array
    {
        return $this->ignoreLogin;
    }

    /**
     * @param mixed $ignoreLogin
     */
    public function setIgnoreLogin($ignoreLogin): void
    {
        $this->ignoreLogin = $ignoreLogin;
    }

    /**
     * @return mixed
     */
    public function getIgnoreAuth(): array
    {
        return $this->ignoreAuth;
    }

    /**
     * @param mixed $ignoreAuth
     */
    public function setIgnoreAuth($ignoreAuth): void
    {
        $this->ignoreAuth = $ignoreAuth;
    }

    /**
     * @return mixed
     */
    public function getIgnoreToken(): array
    {
        return $this->ignoreToken;
    }

    /**
     * @param mixed $ignoreToken
     */
    public function setIgnoreToken($ignoreToken): void
    {
        $this->ignoreToken = $ignoreToken;
    }

    /**
     * @return mixed
     */
    public function getPermission(): array
    {
        return $this->permission;
    }

    /**
     * @param mixed $permission
     */
    public function setPermission($permission): void
    {
        $this->permission = $permission;
    }

    /**
     * @return mixed
     */
    public function getUser(): ?object
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request): void
    {
        $this->request = $request;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route): void
    {
        $this->route = $route;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }


}