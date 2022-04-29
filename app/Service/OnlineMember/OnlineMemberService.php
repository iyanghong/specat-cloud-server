<?php


namespace App\Service\OnlineMember;


use App\Exceptions\NoLoginException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OnlineMemberService
{
    /**
     *
     * @param Request $request
     * @date : 2021/5/14 15:39
     * @author : 孤鸿渺影
     * @return OnlineMemberInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle(Request $request): OnlineMemberInterface
    {

        $onlineMember = new OnlineMember();
        $bearerAuthorization = \request()->header('Authorization');

        if (preg_match('/Bearer\s(\S+)/', $bearerAuthorization, $matches)) {
            $bearerToken = json_decode(maskCrypt()->decrypt($matches[1]), true);

            if (!$bearerToken) {
                return $this->setDefaultMember($onlineMember);
            }

            return $this->memberResolver($onlineMember, $bearerToken);
        }
        return $this->setDefaultMember($onlineMember);


    }


    /**
     *
     * @param OnlineMember $onlineMember
     * @param array $authorization
     * @date : 2021/5/14 15:39
     * @author : 孤鸿渺影
     * @return OnlineMemberInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function memberResolver(OnlineMember $onlineMember, array $authorization): OnlineMemberInterface
    {
        $key = "online:{$authorization['token']}";
        $user = json_decode(Cache::get($key), true);
        if ($user) {


            $onlineMember->setOnline($user);
           /* $onlineMember->setId($user['user_id']);
            $onlineMember->setUuid($user['user_uuid']);
            $onlineMember->setName($user['user_name']);
            $onlineMember->setToken($user['token']);
            $onlineMember->setEmail($user['user_email'] ?? '');
            $onlineMember->setPhone($user['user_phone'] ?? '');

            $isSupperAdmin = false;
            $supperAdminRoleId = (int) systemConfig()->get('Sys.SupperAdminRole');
            foreach ($user['role_id_list'] as $role_id) {
                if ($role_id === $supperAdminRoleId) {
                    $isSupperAdmin = true;
                }
            }
            $onlineMember->setRoles($user['role_id_list']);
            unset($user['role_id_list']);
            $onlineMember->setRoleName($user['role_name_list']);
            unset($user['role_name_list']);
            $onlineMember->setUser($user);
            $onlineMember->setIsSupperAdmin($isSupperAdmin);*/

        } else {
            $onlineMember->setId(-1);
            $onlineMember->setUuid('');

        }


        return $onlineMember;
    }

    /**
     * 未登录
     * @param OnlineMember $member
     * @return OnlineMemberInterface
     */
    private function setDefaultMember(OnlineMember $member): OnlineMemberInterface
    {
        $member->setId(-1);
        $member->setUuid('');
        return $member;
    }

}