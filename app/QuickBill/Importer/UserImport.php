<?php


namespace App\QuickBill\Importer;




use App\Core\Excel\Importer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserImport extends Importer
{
    protected $model = User::class;
    public function formatter(array $row)
    {
        $sex = 0;
        switch ($row[1]){
            case '男':
                $sex = 1;
                break;
            case  '女':
                $sex = 2;
                break;
        }
        return [
            'user_name' => $row[0],
            'use_sex' => $sex,
            'user_pwd' => Hash::make($row[2]),
            'user_header' => $row[3],
        ]; // TODO: Change the autogenerated stub
    }
}