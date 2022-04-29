<?php

namespace App\Jobs\Account;

use App\Models\Member\AccountCancellation;
use App\Models\Member\UserRole;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CancellationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $userUuid;
    private $content = '';
    private $remark = '';

    /**
     * CancellationJob constructor.
     * @param $userUuid
     * @param string $content
     * @param string $remark
     */
    public function __construct($userUuid,$content = '',$remark = '')
    {
        //
        $this->userUuid = $userUuid;
        $this->content = $content;
        $this->remark = $remark;
    }

    /**
     *
     * @date : 2021/5/13 22:02
     * @author : 孤鸿渺影
     * @throws \Throwable
     */
    public function handle()
    {
        //
        DB::beginTransaction();
        $userModel = new User();
        $user = $userModel->findUuid($this->userUuid);


        //删除用户角色
        /** @var  $userRoleModel Builder */
        $userRoleModel = new UserRole();
        $userRoleModel->where(['user_uuid' => $user->user_uuid])->delete();

        /** @var  $accountCancellationModel Builder */
        $accountCancellationModel = new AccountCancellation();
        $cancellationData = [
            'register_at' => strtotime($user->created_at),
            'uuid' => getUuid(),
            'content' => $this->content,
            'remark' => $this->remark
        ];
        $cancellationData = array_merge($cancellationData,$user->toArray());

        $accountCancellationModel->create($cancellationData);
        $user->delete();
        DB::commit();
    }
}
