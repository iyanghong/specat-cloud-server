<?php


namespace App\Service\OnlineMember;


use App\Core\Constructors\Model\BaseModel;
use App\Core\Enums\ErrorCode;
use App\Exceptions\NoLoginException;
use App\Models\Member\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class OnlineMember implements OnlineMemberInterface
{
    private $id = -1;

    private $name = '';

    private $uuid = '';

    private $token = '';

    private $email = '';

    private $phone = '';

    private ?array $roles = [];

    private ?array $roleName = [];

    private bool $isSupperAdmin = false;

    private ?array $user = [];


    /**
     * 登录
     * @param string $token
     * @param Model $user
     * @param null $oldToken
     * @return array
     */
    public function online(string $token, Model $user, $oldToken = null):array
    {
        $key = "online:";

        if ($oldToken !== null) {
            Cache::forget($key . $oldToken);
        }
        $user = $user->toArray();

        /* @var $roleModel Builder */
        $roleModel = new Role();
        $roleList = $roleModel->join('user_role', 'user_role.role_uuid', '=', 'role.role_uuid')->where([
            'user_role.user_uuid' => $user['user_uuid']
        ])->get();

        $roleIdList = [];
        $roleNameList = [];

        foreach ($roleList as $role) {
            $roleIdList[] = $role['role_id'];
            $roleNameList[] = $role['role_name'];
        }
        unset($user['user_pwd']);
        $user['role_id_list'] = $roleIdList;
        $user['role_name_list'] = $roleNameList;

        $this->setOnline($user);
        Cache::put($key . $token, json_encode($user));
        return $user;
    }


    public function logout(): void
    {
        if ($this->token) {
            Cache::forget("online:" . $this->token);
        }

    }

    /**
     * 获取 bearer 验证token
     * @return string
     */
    public function getBearerAuth(): string
    {
        $data = [
            'user_id' => $this->id,
            'token' => $this->token,
            "login_time" => $this->user['login_time'],
            'login_expire' => $this->user['login_expire']
        ];
        $token = maskCrypt()->encrypt(json_encode($data));
        return 'Bearer ' . $token;
    }


    public function setOnline(array $user): void
    {
        $this->id = $user['user_id'];
        $this->uuid = $user['user_uuid'];
        $this->name = $user['user_name'];
        $this->token = Cache::get('token:'.$user['user_id']);
        $this->phone = $user['user_phone'] ?? '';
        $this->email = $user['user_email'] ?? '';

        if (isset($user['isSupperAdmin'])) {
            $this->isSupperAdmin = $user['isSupperAdmin'];
        } else {
            $supperAdminRoleId = systemConfig()->get('Sys.SupperAdminRole');
            foreach ($user['role_id_list'] as $role_id) {
                if ($role_id == $supperAdminRoleId) {
                    $this->isSupperAdmin = true;
                }
            }
        }

        $this->roles = $user['role_id_list'];
        unset($user['role_id_list']);
        $this->roleName = $user['role_name_list'];
        unset($user['role_name_list']);
        $this->user = $user;
    }

    /**
     * 获取用户Model
     * @param array $attribute
     * Date : 2021/4/19 17:05
     * Author : 孤鸿渺影
     * @return Model
     */
    public function getUserModel(array $attribute = ['*']) : Model
    {
        /* @var $userModel Builder */
        $userModel = new User();
        return $userModel->find($this->id,$attribute);
    }
    public function isLogin(): bool
    {
        return $this->token !== '';
    }

    /**
     * 拦截未登录
     * Date : 2021/4/19 22:38
     * Author : 孤鸿渺影
     * @return bool
     * @throws NoLoginException
     */
    public function loginIntercept()
    {
        if($this->id <= 0 || $this->token === ''){
            throw new NoLoginException('未登录',ErrorCode::$ENUM_NO_LOGIN_ERROR);
        }
        //验证到期
        $isExpire = !($this->user['login_expire'] === -1) && strtotime($this->user['login_time']) + $this->user['login_expire'] > time();
        if(!$isExpire){
            throw new NoLoginException('登陆过期',ErrorCode::$ENUM_LOGIN_OVERDUE_ERROR);
        }
        return true;
    }

    /**
     *
     * Date : 2021/4/19 22:49
     * Author : 孤鸿渺影
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function refresh():array
    {
        $user = $this->getUserModel();
        $token = Cache::get('token:'.$user->user_id);
        return $this->online($token,$user,$token);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles ?? [];
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getRoleName(): array
    {
        return $this->roleName ?? [];
    }

    /**
     * @param array $roleName
     */
    public function setRoleName(array $roleName): void
    {
        $this->roleName = $roleName;
    }

    /**
     * @return bool
     */
    public function isSupperAdmin(): bool
    {
        return $this->isSupperAdmin;
    }

    /**
     * @param bool $isSupperAdmin
     */
    public function setIsSupperAdmin(bool $isSupperAdmin): void
    {
        $this->isSupperAdmin = $isSupperAdmin;
    }

    /**
     * @return array
     */
    public function getUser(): array
    {
        return $this->user ?? [];
    }

    /**
     * @param array $user
     */
    public function setUser(array $user): void
    {
        $this->user = $user;
    }


}
