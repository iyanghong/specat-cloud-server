<?php


namespace App\Service\Message\Driver;


use App\Mail\ErrorPwd;
use App\Mail\RegisterVerfiyCodeMail;
use App\Mail\VerifyCodeMail;
use App\Models\User;
use App\Service\Message\MessageInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use mysql_xdevapi\Exception;

class MessageEmail implements MessageInterface
{
    private String $siteName;

    /**
     * 注册账户邮箱验证码
     * @param string $address
     * Date : 2021/4/20 19:33
     * Author : 孤鸿渺影
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function sendRegisterVerifyCode(string $address = '')
    {
        $code = mt_rand(100000, 999999);
        $res = [
            'flag' => false,
            'msg' => '',
            'code' => $code
        ];
        $site = $this->siteName;
        $title = '[' . $site . '] 注册验证码';
        $Mailable = new RegisterVerfiyCodeMail();
        $Mailable->build([
            'site' => $site,
            'code' => $code,
        ]);
        $cacheData = [
            'code' => $code,
            'time' => time()
        ];
        Cache::put('Mail_RegisterVerifyCode:' . $address, json_encode($cacheData));

        $Mailable->to($address)->subject($title);
        Mail::to($address)->send($Mailable);
        $res['flag'] = true;
        return $res;
    }

    /**
     * 发送验证码
     * @param string $address
     * @param string $name
     * Date : 2021/4/20 19:32
     * Author : 孤鸿渺影
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function verifyCode(string $address = '', string $name = '')
    {
        $code = mt_rand(100000, 999999);
        $res = [
            'flag' => false,
            'msg' => '',
            'code' => $code
        ];
        $site = $this->siteName;
        if (empty($address)) {
            $address = onlineMember()->getEmail();
            if (empty($address)) {
                $res['msg'] = '该账户未绑定邮箱，无法发送';
                return $res;
            }
            $name = onlineMember()->getName();
        } else {
            /* @var $userModel \Illuminate\Database\Eloquent\Builder */
            $userModel = new User();
            $user = $userModel->where(['user_email' => $address])->first();
            if (!$user) {
                if (onlineMember()->isLogin()) {
                    $user = onlineMember()->getUserModel();
                } else {
                    $res['msg'] = '该账户未注册';
                    return $res;
                }
            }else{
                $address = $user['user_email'];
            }

            $name = $user['user_name'];
        }
        $title = '[' . $site . '] 验证码';
        $Mailable = new VerifyCodeMail();
        $Mailable->build([
            'site' => $site,
            'name' => $name,
            'code' => $code,
            'account' => $address
        ]);
        $cacheData = [
            'user_id' => $user['user_id'],
            'user_uuid' => $user['user_uuid'],
            'code' => $code,
            'time' => time()
        ];
        Cache::put('VerifyCode_Mail:' . $address, json_encode($cacheData));
        $Mailable->to($address)->subject($title);
        Mail::to($address)->send($Mailable);
        $res['flag'] = true;
        return $res;
    }

    public function changeVerifyCode()
    {
    }

    /**
     * 获取验证码
     * @param string $address
     * @date : 2021/5/12 20:47
     * @return mixed
     * @throws MessageException
     * @author : 孤鸿渺影
     */
    public function getCode(string $address = '')
    {
        if (empty($address)) {
            $address = onlineMember()->getEmail();
            if (empty($address)) {
                throw new MessageException('该账户未绑定邮箱，无法发送');
            }
        }
        $data = json_decode(Cache::get('VerifyCode_Mail:' . $address), true);
        if (!$data) {
            throw new MessageException('请发送验证码');
        }
        $time = time() - $data['time'];
        if ($time > 60 * 10) {
            throw new MessageException('验证码超时');
        }
        return $data;
    }

    public function errorPwd($user, $content = '')
    {
        if (empty($user['user_email'])) return false;
        $address = $user['user_email'];
        $name = $user['user_name'];
        $site = $this->siteName;
        $title = '[' . $site . '] 账号已冻结';

        $Mailable = new ErrorPwd();
        $Mailable->build([
            'site' => $site,
            'name' => $name,
            'content' => $content,
            'account' => $address
        ]);
        $Mailable->to($address)->subject($title);
        Mail::to($address)->send($Mailable);
        return true;

    }

    /**
     * @param mixed $siteName
     */
    public function setSiteName($siteName): void
    {
        $this->siteName = $siteName;
    }

}
