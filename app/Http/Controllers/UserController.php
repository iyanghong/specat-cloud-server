<?php

namespace App\Http\Controllers;

use App\Core\Enums\ErrorCode;
use App\Exceptions\NoLoginException;

use App\Models\Cloud\Disk;
use App\Models\Log\LogUserStatus;
use App\Models\Member\Role;
use App\Models\Member\UserRole;
use App\Service\Auth\RoleAuthDevice;
use App\Service\Auth\RoleMenuDevice;
use App\Service\Message\Driver\MessageException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;


/**
 * 用户
 * @date : 2021/5/8 23:17
 * @author : 孤鸿渺影
 * @package App\Http\Controllers
 */
class UserController extends Controller
{


    /**
     * 查询用户
     * @param $account
     * @date : 2021/5/8 23:16
     * @return string
     * @author : 孤鸿渺影
     */
    public function searchAccount($account): string
    {
        $accountField = 'user_id';
        if (preg_match('/^0?(13\d|14[5,7]|15[0-3,5-9]|17[0,6-8]|18\d)\d{8}$/', $account)) {
            $accountField = 'user_phone';
        } else if (preg_match('/^[_a-z0-9-\.]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $account)) {
            $accountField = 'user_email';
        }
        /* @var $userModel Builder */
        $userModel = new User();
        $user = $userModel->where([
            $accountField => $account
        ])->first();

        if (!$user) {
            return api_response_action(false, ErrorCode::$ENUM_NO_DATA_ERROR, '无该账户');
        }

        $log = $user->user_ip == request()->ip() ? 1 : 0;
        if (request()->input('mod') == 'verify') {
            $resData = [
                'user_id' => $user->user_id,
                'user_header' => $user->user_header,
                'login_time' => $user->login_time ?? 0,
                'user_name' => $user->user_name,
                'user_phone' => $user->user_phone,
                'user_email' => $user->user_email,
                'ip_login' => $log
            ];
        } else {
            $resData = [
                'user_id' => $user->user_id,
                'user_header' => $user->user_header,
                'login_time' => $user->login_time ?? 0,
                'ip_login' => $log
            ];
        }
        return api_response_show($resData, ErrorCode::$ENUM_SUCCESS, '查询成功');

    }

    /**
     * 修改用户登录过期时长
     * @date : 2021/7/15 16:11
     * @param $time
     * @return string
     * @throws NoLoginException
     * @author : 孤鸿渺影
     */
    public function updateLoginExpire($time): string
    {

        if (!is_numeric($time)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR);
        }
        onlineMember()->loginIntercept();

        $user = onlineMember()->getUserModel();
        $user->login_expire = $time;
        if ($user->save()) {
            return api_response_action(true);
        }
        return api_response_action(false);

    }

    /**
     * 查询登录
     * @date : 2021/5/8 23:16
     * @return string
     * @author : 孤鸿渺影
     */
    public function checkLogin(): string
    {

        if (!onlineMember()->isLogin()) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '未登录');
        }
        $loginExpire = onlineMember()->getUser()['login_expire'] ?? 0;
        $loginTime = onlineMember()->getUser()['login_time'] ?? 0;
        $loginTime = strtotime($loginTime);
        if ($loginExpire) {
            $isExpire = !($loginExpire === -1) && $loginTime + $loginExpire > time();
            if (!$isExpire) {
                return api_response_action(false, ErrorCode::$ENUM_SUCCESS, '登录过期');
            }
        }

        $count = (new Disk())->getUserCount(onlineMember()->getUuid());

        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '已登录', [
            'sign_disk' => $count
        ]);
    }

    /**
     * 用户登录
     * @date : 2021/5/8 23:16
     * @return string
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function login(): string
    {
        $account = request()->input('account');
        $password = request()->input('password');
        if (empty($account) || empty($password)) {
            //账号或密码错误
            return api_response_action(false, ErrorCode::$ENUM_ACCOUNT_PASSWORD_ERROR);
        }
        $accountField = 'user_id';
        if (preg_match('/^0?(13\d|14[5,7]|15[0-3,5-9]|17[0,6-8]|18\d)\d{8}$/', $account)) {
            $accountField = 'user_phone';
        } else if (preg_match('/^[_a-z0-9-\.]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $account)) {
            $accountField = 'user_email';
        }


        /* @var $userModel Builder */
        $userModel = new User();
        $user = $userModel->where([
            $accountField => $account
        ])->first();
        if (!$user) {
            //账号或密码错误
            return api_response_action(false, ErrorCode::$ENUM_ACCOUNT_PASSWORD_ERROR);
        }
        if ($user->user_status === 4) {
            return api_response_action(false, ErrorCode::$ENUM_ACCOUNT_EXCEPTION, "账号正在注销当中,无法登录");
        }
        if ($user->user_status === 2) {
            return api_response_action(false, ErrorCode::$ENUM_ACCOUNT_EXCEPTION, "账号已被冻结,无法登录");
        }
        /* @var $user User */
        if (!Hash::check($password, $user->user_pwd)) {
            //账号或密码错误,开始计算密码错误次数
            $user->error_num = $user->error_num + 1;
            $msg = '密码已连续错误' . $user->error_num . '次';
            if ($user->error_num >= 5 && $user->user_status !== 2 && $user->status_uuid == '') {
                /* @var $logUserStatus Builder */
                $logUserStatus = new LogUserStatus();
                $logStatus = $logUserStatus->create([
                    'status_uuid' => getUuid(),
                    'user_uuid' => $user->user_uuid,
                    'content' => '密码连续错误5次，已进行冻结保护!',
                    'ip' => request()->ip(),
                    'user_status' => 2,
                    'created_at' => date('Y-m-d H:i:s'),
                    'status_time' => 0
                ]);
                if ($logStatus) {
                    $user->user_status = 2;
                    $user->status_uuid = $logStatus->status_uuid;
                }
                $msg .= '，已被冻结';
            }
            $user->save();
            return api_response_action(false, ErrorCode::$ENUM_ACCOUNT_PASSWORD_ERROR, $msg);
        }
        //检查账户状态
        if ($user->user_status !== 1) {
            $statusText = match ($user->user_status) {
                0 => '账号已失效',
                2 => '账号已被冻结',
                3 => '账号存在违规，暂时无法登陆',
                default => ""
            };
            return api_response_action(false, ErrorCode::$ENUM_ACCOUNT_EXCEPTION, $statusText);
        }


        $oldToken = Cache::get('token:' . $user->user_id);

//        $token = JWTAuth::fromUser($user);
        /*        $token = auth()->attempt([
                    $accountField => $account,
                    'password' => $password
                ]);
                echo $token;*/

        $newToken = getUserToken($user->user_id);
        Cache::put('token:' . $user->user_id, $newToken);
        $user->user_ip = request()->ip();
        $user->login_time = date('Y-m-d H:i:s');
        $user->error_num = 0;
        $user->save();


        $user = onlineMember()->online($newToken, $user, $oldToken);
        $user['token'] = onlineMember()->getBearerAuth();
        $roleAuth = new RoleAuthDevice();
        $roleMenu = new RoleMenuDevice();

        $roleIdList = onlineMember()->getRoles();
        $user['auth_api_list'] = $roleAuth->getApiList($roleIdList);
        $user['menu_list'] = $roleMenu->getUserMenu($roleIdList);
        $user['auth_page_list'] = $roleAuth->getPage($roleIdList);
        //查看当前用户所有硬盘数量
        $user['sign_disk'] = (new Disk())->getUserCount($user['user_uuid']);

        return api_response_action(true, 0, '登录成功', $user);
    }


    /**
     * 登出系统
     * @date : 2021/6/14 20:45
     * @return string
     * @throws NoLoginException
     * @author : 孤鸿渺影
     */
    public function logout(): string
    {

        onlineMember()->loginIntercept();
        onlineMember()->logout();
        return api_response_action(true, 0, '登出成功');
    }

    /**
     * 修改密码
     * @date : 2021/5/8 23:16
     * @return string
     * @author : 孤鸿渺影
     */
    public function changePassword(): string
    {
        if (!onlineMember()->isLogin()) {
            return api_response_action(false, ErrorCode::$ENUM_NO_LOGIN_ERROR);
        }
        $oldPwd = request()->input('old_pwd');
        $newPwd = request()->input('new_pwd');
        $validate = Validator::make([
            'new_pwd' => $newPwd
        ], [
            'new_pwd' => 'min:6|max:16'
        ], [], [
            'new_pwd' => '密码'
        ]);
        if ($validate->fails()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validate->errors()->first());
        }
        $uuid = onlineMember()->getUuid();
        /* @var $userModel Builder */
        $userModel = new User();
        /* @var $user Model */
        $user = $userModel->where(['user_uuid' => $uuid])->first();

        if (Hash::check($oldPwd, $user->user_pwd)) {
            $user->user_pwd = Hash::make($newPwd);

            $user->save();
            return api_response_action(true);
        }
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '原密码错误');
    }

    /**
     * 重置密码
     * @param $uuid
     * @date : 2021/5/8 23:16
     * @return string
     * @author : 孤鸿渺影
     */
    public function resetPassword($uuid): string
    {
        if (!onlineMember()->isSupperAdmin()) {
            return api_response_action(false, ErrorCode::$ENUM_API_NO_AUTH_ERROR);
        }
        /* @var $userModel Builder */
        $userModel = new User();
        /* @var $user Model */
        $user = $userModel->where(['user_uuid' => $uuid])->first();
        if (!$user) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '用户不存在');
        }
        $defaultPwd = rand(100000, 999999);
        $user->user_pwd = Hash::make($defaultPwd);
        $user->save();
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '密码重置成功', [
            'password' => $defaultPwd
        ]);
    }

    /**
     * 获取修改用户信息
     * @date : 2021/5/8 23:16
     * @return string
     * @author : 孤鸿渺影
     */
    public function getEditAccountData(): string
    {
        if (!onlineMember()->isLogin()) { //未登录
            return api_response_show(false, ErrorCode::$ENUM_NO_LOGIN_ERROR);
        }
        $user = onlineMember()->getUserModel(['user_uuid', 'user_id', 'user_name', 'user_info', 'user_birthday', 'user_header', 'user_sex', 'user_address']);
        $user->user_birthday = date('Y-m-d h:i:s', $user->user_birthday);
        return api_response_show($user);
    }


    /**
     * 修改基本用户信息
     * @date : 2021/5/8 23:16
     * @return string
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function updateBaseAccount(): string
    {
        if (!onlineMember()->isLogin()) { //未登录
            return api_response_show(false, ErrorCode::$ENUM_NO_LOGIN_ERROR);
        }

        $data = request()->only(['user_name', 'user_info', 'user_birthday', 'user_header', 'user_sex', 'user_address']);

        $validate = Validator::make($data, [
            'user_name' => 'required',
            'user_sex' => 'required',
            'user_birthday' => 'required',
            'user_header' => 'required',
            'user_info' => 'required',
            'user_address' => 'required',

        ], [], [
            'user_name' => '用户昵称',
            'user_sex' => '用户性别',
            'user_birthday' => '用户生日',
            'user_header' => '用户头像',
            'user_info' => '个性签名',
            'user_address' => '用户地址',
        ]);
        if ($validate->fails()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validate->errors()->first());
        }
        $user = onlineMember()->getUserModel();
        if (str_contains($data['user_header'], 'cache/')) {
            $headerPath = 'users/' . $user->user_id . '/data/header.' . explode('.', $data['user_header'])[1];
            cloudDisk()->move($data['user_header'], $headerPath);
            $data['user_header'] = $headerPath;
        }
//        if (!is_numeric($data['user_birthday'])) {
//            $data['user_birthday'] = strtotime($data['user_birthday']);
//        }
        $flag = $user->update($data);

        if (!$flag) {
            return api_response_action(false);
        }

        return api_response_action(true);
    }

    /**
     * 刷新账户
     * @date : 2021/5/8 23:16
     * @return string
     * @throws NoLoginException
     * @author : 孤鸿渺影
     */
    public function refreshAccount(): string
    {
        onlineMember()->loginIntercept();
        $user = onlineMember()->refresh();
        $user['token'] = onlineMember()->getBearerAuth();
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '刷新成功', $user);
    }


    /**
     * 修改手机号码
     * @date : 2021/5/8 23:16
     * @return string
     * @throws NoLoginException
     * @author : 孤鸿渺影
     */
    public function updatePhone(): string
    {
        onlineMember()->loginIntercept();
        $oldPhone = request()->input('old_phone');
        $userPhone = request()->input('new_phone');
        if (!preg_match('/^0?(13\d|14[5,7]|15[0-3,5-9]|17[0,6-8]|18\d)\d{8}$/', $userPhone)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '手机号码格式不正确');
        }
        $user = onlineMember()->getUserModel();
        if ($oldPhone !== $user->user_phone) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '旧手机号码不正确');
        }
        //核查新手机号码已被注册
        $validator = Validator::make(['user_phone' => $userPhone], [
            'user_phone' => Rule::unique('users')->ignore($user->user_id, 'user_id')
        ], [
            'user_phone.unique' => '邮箱已存在'
        ]);
        if ($validator->fails()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->errors()->first());
        }

        $user->user_phone = $userPhone;
        $user->save();
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '修改成功');
    }

    /**
     * 修改电子邮箱
     * @date : 2021/5/14 16:35
     * @return string
     * @throws NoLoginException
     * @throws MessageException
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function updateEmail(): string
    {
        onlineMember()->loginIntercept();
        $email = request()->input('email');
        $oldCode = request()->input('oldCode');
        $newCode = request()->input('newCode');
        if (!$oldCode || !$newCode) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '请输入验证码');
        }
        if (!preg_match('/^[_a-z0-9-\.]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $email)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '电子邮箱格式不正确');
        }

        $user = onlineMember()->getUserModel();
        $oldMessageEmail = messageMail()->getCode($user->user_email);
        if ($oldMessageEmail['code'] != $oldCode) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '旧邮箱验证码验证失败');
        }
        $newMessageEmail = messageMail()->getCode($email);

        if ($newMessageEmail['code'] != $newCode) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '新邮箱验证码验证失败');
        }
        //核查新电子邮箱号码已被注册
        $validator = Validator::make(['user_email' => $email], [
            'user_email' => Rule::unique('users')->ignore($user->user_id, 'user_id')
        ], [
            'user_email.unique' => '邮箱已存在'
        ]);
        if ($validator->fails()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->errors()->first());
        }

        $user->user_email = $email;
        $user->save();
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '修改成功');
    }


    /**
     * 修改用户状态
     * @date : 2021/5/8 23:17
     * @return string
     * @throws Throwable
     * @author : 孤鸿渺影
     */
    public function updateStatus(): string
    {
        $userStatus = (int)request()->input('user_status');
        $uuid = request()->input('user_uuid');

        /* @var $userModel Builder */
        $userModel = new User();
        $user = $userModel->where(['user_uuid' => $uuid])->first();

        if (!$user) {
            return api_response_action(false, ErrorCode::$ENUM_NO_DATA_ERROR, '用户不存在');
        }
        $statusUuid = request()->input('status_uuid');
        /* @var $userStatusModel Builder */
        $userStatusModel = new LogUserStatus();

        //恢复状态操作
        if (!empty($statusUuid)) {
            if ($user->status_uuid === $statusUuid) {
                $user->update([
                    'user_status' => 1,
                    'status_uuid' => null
                ]);
            }
            $userStatusModel->where([
                'status_uuid' => $statusUuid
            ])->update([
                'recover' => 1,
                'remark' => request()->input('remark', '')
            ]);
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '恢复成功');
        }
        if ($userStatus === 1) {

            $flag = $user->update([
                'user_status' => 1,
                'status_uuid' => null
            ]);
            if ($flag) {
                $userStatusModel->where([
                    'status_uuid' => $user->status_uuid
                ])->update([
                    'recover' => 1,
                    'remark' => request()->input('remark', '')
                ]);
                return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '修改状态成功');
            }
            return api_response_action(false);
        }

        if (!in_array($userStatus, [0, 1, 2, 3])) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '状态错误');
        }
        DB::beginTransaction();
        $content = request()->input('content');
        $statusTime = request()->input('status_time', 0);
        if (empty($content)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '请输入状态内容');
        }

        $statusUuid = getUuid();
        $status = $userStatusModel->create([
            'status_uuid' => $statusUuid,
            'user_uuid' => $uuid,
            'content' => $content,
            'ip' => request()->ip(),
            'created_at' => time(),
            'user_status' => $userStatus,
            'status_time' => $statusTime
        ]);
        if (!$status) {
            DB::rollBack();
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '修改失败,error:status');
        }

        $flag = $user->update([
            'user_status' => $userStatus,
            'status_uuid' => $statusUuid
        ]);

        if ($flag) {
            DB::commit();
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '修改状态成功');
        }
        DB::rollBack();
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '修改失败,error:user');
    }

    /**
     * 获取用户状态
     * @param $account
     * @date : 2021/5/8 23:17
     * @return string
     * @author : 孤鸿渺影
     */
    public function getUserStatus($account): string
    {

        $mod = 'user_id';
        if (request()->input('type') === 'user_uuid') {
            $mod = 'user_uuid';
        } else {
            //判断账号类型
            if (preg_match('/^0?(13\d|14[5,7]|15[0-3,5-9]|17[0,6-8]|18\d)\d{8}$/', $account)) {
                $mod = 'user_phone';
            } else if (preg_match('/^[_a-z0-9-\.]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $account)) {
                $mod = 'user_email';
            }
        }

        $mod = 'users.' . $mod;
        /* @var $userModel Builder */
        $userModel = new User();
        $user = $userModel->where([$mod => $account])->first();
        if (!$user) {
            return api_response_show(null, ErrorCode::$ENUM_NO_DATA_ERROR, '用户不存在');
        }


        if (!$user->status_uuid && $user->user_status === 1) {
            return api_response_show([
                'user_status' => 1
            ], ErrorCode::$ENUM_SUCCESS, '用户状态正常');
        }
        if ($user->user_status === 4) {
            return api_response_show([
                'user_status' => 4,
                'content' => '账户正在注销中无法登录',
                'user_name' => $user->user_name,
                'user_email' => $user->user_email,
                'user_phone' => $user->user_phone
            ], ErrorCode::$ENUM_SUCCESS, '账户正在注销中无法登录');
        }

        /* @var $userStatusModel Builder */
        $userStatusModel = new LogUserStatus();
        $userStatus = $userStatusModel
            ->join('users', 'users.user_uuid', '=', 'log_user_status.user_uuid')
            ->where([$mod => $account])
            ->first([
                'users.user_id',
                'users.user_uuid',
                'users.user_name',
                'users.user_phone',
                'users.user_email',

            ]);
        if ($userStatus === null) {
            $user->user_status = 1;
            $user->status_uuid = null;
            $user->save();
            return api_response_show([
                'user_status' => 1
            ], ErrorCode::$ENUM_SUCCESS, '用户状态正常');
        }
        return api_response_show($userStatus->toArray());
    }

    /**
     * 检查邮箱号码是否可以注册
     * @param $email
     * @date : 2021/5/8 23:17
     * @return string
     * @author : 孤鸿渺影
     */
    public function checkEmail($email): string
    {
        if (!preg_match('/^[_a-z0-9-\.]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $email)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '邮箱格式不正确');
        }
        //核查新电子邮箱号码已被注册
        $validator = Validator::make(['user_email' => $email], [
            'user_email' => Rule::unique('users')
        ], [
            'user_email.unique' => '邮箱已存在'
        ]);
        if ($validator->fails()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->errors()->first());
        }
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '邮箱可以使用');
    }


    /**
     * 检查手机号码是否可以注册
     * @param $phone
     * @date : 2021/5/8 23:17
     * @return string
     * @author : 孤鸿渺影
     */
    public function checkPhone($phone): string
    {
        if (!preg_match('/^0?(13\d|14[5,7]|15[0-3,5-9]|17[0,6-8]|18\d)\d{8}$/', $phone)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '手机号码格式不正确');
        }
        //核查新手机号码已被注册
        $validator = Validator::make(['user_phone' => $phone], [
            'user_phone' => Rule::unique('users')
        ], [
            'user_phone.unique' => '邮箱已存在'
        ]);
        if ($validator->fails()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->errors()->first());
        }
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '手机号码可以使用');
    }

    /**
     * 发送邮箱注册码
     * @param $email
     * @date : 2021/5/8 23:17
     * @return string
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function sendRegisterEmailVerifyCode($email): string
    {
        if (!preg_match('/^[_a-z0-9-\.]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $email)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '邮箱格式不正确');
        }
        //核查新电子邮箱号码已被注册
        $validator = Validator::make(['user_email' => $email], [
            'user_email' => Rule::unique('users')
        ], [
            'user_email.unique' => '邮箱已存在'
        ]);
        if ($validator->fails()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->errors()->first());
        }

        $data = Cache::get("Mail_RegisterVerifyCode:$email");
        $data = json_decode($data, true);
        sleep(0.5);
        if ($data !== null) {
            $nowTime = time();
            $time = ($nowTime - $data['time']);
            if ($time < 60) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '请勿频繁操作,' . (60 - $time) . 's');
            }
        }

        $message = messageMail()->sendRegisterVerifyCode($email);

        if ($message['flag']) {
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '发送成功');
        }
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, $message['msg']);
    }

    /**
     * 发送邮箱验证码
     * @date : 2021/5/12 23:11
     * @return string
     * @throws NoLoginException
     * @throws InvalidArgumentException
     * @author : 孤鸿渺影
     */
    public function sendEmailVerifyCode(): string
    {
        onlineMember()->loginIntercept();
        $email = onlineMember()->getEmail();
        return $this->doSendEmail($email);
    }

    /**
     * 发送换绑邮箱号码
     * @date : 2021/5/14 16:39
     * @param $email
     * @return string
     * @throws InvalidArgumentException
     * @throws NoLoginException
     * @author : 孤鸿渺影
     */
    public function sendChangeEmailVerifyCode($email): string
    {
        onlineMember()->loginIntercept();
        if (!preg_match('/^[_a-z0-9-\.]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $email)) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '邮箱格式不正确');
        }
        //核查新电子邮箱号码已被注册
        $validator = Validator::make(['user_email' => $email], [
            'user_email' => Rule::unique('users')
        ], [
            'user_email.unique' => '邮箱已存在'
        ]);
        if ($validator->fails()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->errors()->first());
        }
        return $this->doSendEmail($email);
    }

    /**
     * 验证校验码
     * @date : 2021/5/13 17:47
     * @param $code
     * @return string
     * @throws NoLoginException
     * @author : 孤鸿渺影
     */
    public function verifyEmailCode($code): string
    {
        onlineMember()->loginIntercept();
        if (request()->exists('email')) {
            $email = request()->input('email');
            if (!preg_match('/^[_a-z0-9-\.]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $email)) {
                return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, '邮箱格式不正确');
            }
        } else {
            $email = onlineMember()->getEmail();
        }
        $data = Cache::get("VerifyCode_Mail:$email");
        $data = json_decode($data, true);
        if ($data === null) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '请发送验证码');
        }
        if ($data['code'] == $code) {
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '验证成功');
        }
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '验证码错误');

    }

    /**
     * 邮箱注册
     * @date : 2021/5/8 23:17
     * @return string
     * @throws InvalidArgumentException
     * @throws Throwable
     * @author : 孤鸿渺影
     */
    public function registerEmail(): string
    {
        $data = request()->only(['user_email', 'user_name', 'verify_code', 'user_pwd']);
        $validator = Validator::make($data, [
            'user_email' => 'required|email:rfc,dns|unique:users,user_email',
            'user_name' => 'required',
            'verify_code' => 'required|numeric',
            'user_pwd' => 'required|min:6|max:16',
        ], [
            'user_email.required' => '邮箱不能为空',
            'user_email.email' => '电子邮箱格式不正确',
            'user_name.required' => '用户名不能为空',
            'verify_code.required' => '邮箱验证码不能为空',
            'verify_code.numeric' => '邮箱验证码应该为纯数字',
            'verify_code.size' => '邮箱验证码长度应该为6个字符',
            'user_pwd.required' => '密码不能为空',
            'user_pwd.min' => '密码不能低于6个字符',
            'user_pwd.max' => '密码不能超过16个字符',
            'user_email.unique' => '邮箱已被注册',
        ]);
        if ($validator->fails()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->errors()->first());
        }

        $verifyCodeCache = Cache::get("Mail_RegisterVerifyCode:" . $data['user_email']);
        $verifyCodeCache = json_decode($verifyCodeCache, true);

        if ($verifyCodeCache === null) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '请先发送验证码');
        }
        $time = time() - $verifyCodeCache['time'];
        //判断验证码是否超过10分钟
        if ($time > 6000) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '邮箱验证码超时');
        }
        if ($verifyCodeCache['code'] != $data['verify_code']) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '邮箱验证码不正确');
        }
        $registerMod = request()->input('mod', null); //注册模式
        $data['create_source'] = match ($registerMod) {
            'Blog' => '博客注册',
            'Develop' => '开发平台注册',
            'specat-cloud' => '云盘注册',
            default => ''
        };
        //默认用户头像
        $data['user_header'] = systemConfig()->get('Sys.DefaultUserHeader', 'global/default_header_1.jpg');
        $data['user_info'] = systemConfig()->get('Sys.DefaultUserInfo', '这个人很懒，什么也没留下~');
        $data['user_uuid'] = getUuid();
        $data['user_ip'] = request()->ip();
        $data['user_pwd'] = Hash::make($data['user_pwd']);//密码加密
        /* @var $userModel Builder */
        $userModel = new User();
        DB::beginTransaction();
        $user = $userModel->create($data);
        if (!$user) {
            DB::rollBack();
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '注册失败');
        }
        //获取默认角色添加
        $role = explode(',', systemConfig()->get('Sys.DefaultRegisterRole', ''));
        //博客注册用户
        if ($registerMod == 'Blog' && !in_array(3, $role)) {
            $role[] = 3;
        }
        //开发平台注册用户
        if ($registerMod == 'Develop' && !in_array(4, $role)) {
            $role[] = 4;
        }
        //快猫云注册用户
        if ($registerMod == 'specat-cloud' && !in_array(4, $role)) {
            $role[] = 9;
        }

        /* @var $userRoleModel Builder */
        $userRoleModel = new UserRole();
        /* @var $roleModel Builder */
        $roleModel = new Role();
        foreach ($role as $value) {
            if (empty($value)) continue;//如果为空值则跳过
            $roleEntity = $roleModel->where(['role_id' => $value])->first();
            if (!$roleEntity) break;
            $flag = $userRoleModel->create([
                'uuid' => getUuid(),
                'user_uuid' => $user->user_uuid,
                'role_uuid' => $roleEntity->role_uuid,
                'auth_time' => time()
            ]);
            if (!$flag) {
                DB::rollBack();
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '用户角色授予失败');
            }
        }
        Cache::forget("Mail_RegisterVerifyCode:" . $data['user_email']);
        DB::commit();
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '注册成功');
    }


    /**
     * 列表
     * @Author:System Generate
     * @Date:2021-04-16 12:19:49
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new User())->index();
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2021-04-16 12:19:49
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        return resourceConstructor(new User())->store($request);
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2021-04-16 12:19:49
     * @param Request $request
     * @return string
     */
    public function get(Request $request): string
    {
        return resourceConstructor(new User())->get($request);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2021-04-16 12:19:49
     * @param $id
     * @return string
     */
    public function show($id): string
    {
        return resourceConstructor(new User())->show($id);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2021-04-16 12:19:49
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        return resourceConstructor(new User())->update($request, $id);
    }

    /**
     * 删除
     * @Author:System Generate
     * @Date:2021-04-16 12:19:49
     * @param $id
     * @return string
     */
    public function destroy($id): string
    {
        return resourceConstructor(new User())->destroy($id);
    }

    /**
     * @param $email
     * @return string
     * @throws InvalidArgumentException
     */
    public function doSendEmail($email): string
    {
        $data = Cache::get("VerifyCode_Mail:$email");
        $data = json_decode($data, true);
        if ($data !== null) {
            $time = (time() - $data['time']);
            if ($time < 60) {
                return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, '请勿频繁操作,' . (60 - $time) . 's');
            }
        }
        $message = messageMail()->verifyCode($email);

        if ($message['flag']) {
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '发送成功');
        }
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, $message['msg'], ['email' => $email]);
    }
}
