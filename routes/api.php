<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** users 资源接口集合 : user */
Route::resource('user', '\App\Http\Controllers\UserController')->names('users');

Route::post('login', '\App\Http\Controllers\UserController@login');
Route::post('logout', '\App\Http\Controllers\UserController@logout');


Route::group(['prefix' => 'account'], function () {

    Route::get('search/{account}', '\App\Http\Controllers\UserController@searchAccount')->name('account.search');
    Route::get('login/check', '\App\Http\Controllers\UserController@checkLogin')->name('account.login.check');

    Route::post('email/code/send', '\App\Http\Controllers\UserController@sendEmailVerifyCode')->name('account.sendEmailVerifyCode');
    Route::post('email/change/code/send/{code}', '\App\Http\Controllers\UserController@sendChangeEmailVerifyCode')->name('account.sendChangeEmailVerifyCode');
    Route::get('email/code/verify/{code}', '\App\Http\Controllers\UserController@verifyEmailCode')->name('account.verifyEmailCode');

    Route::patch('update/password', '\App\Http\Controllers\UserController@changePassword')->name('account.update.password');
    Route::patch('resetPassword/{uuid}', '\App\Http\Controllers\UserController@resetPassword')->name('account.password.reset');
    Route::patch('update/phone', '\App\Http\Controllers\UserController@updatePhone')->name('account.update.phone');
    Route::patch('update/email', '\App\Http\Controllers\UserController@updateEmail')->name('account.update.email');
    Route::patch('update/status', '\App\Http\Controllers\UserController@updateStatus')->name('account.update.status');
    Route::patch('update/loginExpire/{time}', '\App\Http\Controllers\UserController@updateLoginExpire');
//    ->name('account.update.loginExpire')

    Route::patch('update/base', '\App\Http\Controllers\UserController@updateBaseAccount')->name('account.update.base');


    Route::get('check/email/{email}', '\App\Http\Controllers\UserController@checkEmail')->name('account.check.email');
    Route::get('check/phone/{phone}', '\App\Http\Controllers\UserController@checkPhone')->name('account.check.phone');
    Route::post('sendRegisterEmailVerifyCode/{email}', '\App\Http\Controllers\UserController@sendRegisterEmailVerifyCode')->name('account.sendRegisterEmailVerifyCode');

    Route::post('register/email', '\App\Http\Controllers\UserController@registerEmail');

    Route::get('status/{account}', '\App\Http\Controllers\UserController@getUserStatus');

    /* 获取修改用户信息 : account/getEditAccountData */
    Route::get('edit', '\App\Http\Controllers\UserController@getEditAccountData')->name('account.edit');


    Route::get('refreshAccount', '\App\Http\Controllers\UserController@refreshAccount')->name('account.refresh');


});


Route::group(['prefix' => 'member'], function () {

    Route::get('leftMenu/tree', '\App\Http\Controllers\Member\LeftMenuController@getTreeList')->name('member.leftMenu.tree');

    /** 导航菜单 资源接口集合 : member/leftMenu */
    Route::resource('leftMenu', '\App\Http\Controllers\Member\LeftMenuController')->names('member.leftMenu');


    Route::post('permission/role/save', '\App\Http\Controllers\Member\PermissionController@saveUserRoleList')->name('member.permission.role.save');
    Route::get('permission/role/{uuid}', '\App\Http\Controllers\Member\PermissionController@getByRoleList')->name('member.permission.role.list');

    /** permission 资源接口集合 : member/permission */
//	Route::resource('permission','\App\Http\Controllers\Member\PermissionController')->names('member');


    Route::post('roleMenu/role/save', '\App\Http\Controllers\Member\RoleMenuController@saveRoleMenuList')->name('member.roleMenu.save');
    Route::get('roleMenu/role/{uuid}', '\App\Http\Controllers\Member\RoleMenuController@getRoleMenuListByRole')->name('member.roleMenu.getByRole');
    /** 角色菜单 资源接口集合 : member/roleMenu */
    Route::resource('roleMenu', '\App\Http\Controllers\Member\RoleMenuController')->names('member.roleMenu');

    Route::get('ruleGroup/trehorizone', '\App\Http\Controllers\Member\RuleGroupController@listTree')->name('member.ruleGroup.tree');
    Route::get('ruleGroup/rule/tree', '\App\Http\Controllers\Member\RuleGroupController@listRuleTree')->name('member.ruleGroup.rule.tree');

    /** 规则组 资源接口集合 : member/ruleGroup */
    Route::resource('ruleGroup', '\App\Http\Controllers\Member\RuleGroupController')->names('member.ruleGroup');

    Route::post('rule/batch/store', '\App\Http\Controllers\Member\RuleController@batchInsert')->name('member.rule.batch.store');
    Route::post('rule/analyse', '\App\Http\Controllers\Member\RuleController@analyseRule')->name('member.rule.analyse');


    Route::post('route/rule/refresh', '\App\Http\Controllers\Member\RuleController@refreshRouteRule')->name('route.rule.refresh');
    /** 规则 资源接口集合 : member/rule */
    Route::resource('rule', '\App\Http\Controllers\Member\RuleController')->names('member.rule');


    Route::post('userRole/save/{userUuid}', '\App\Http\Controllers\Member\UserRoleController@saveUserRole')->name('user.role.save');

    Route::get('userRole/user/{userUuid}', '\App\Http\Controllers\Member\UserRoleController@listUserRole')->name('user.role.ByUser');
    /** user_role 资源接口集合 : member/userRole */
    Route::resource('userRole', '\App\Http\Controllers\Member\UserRoleController')->names('member.userRole');

    /** role 资源接口集合 : member/role */
    Route::resource('role', '\App\Http\Controllers\Member\RoleController')->names('member.role');

    /** 角色 资源接口集合 : member/role */
    Route::resource('role', '\App\Http\Controllers\Member\RoleController')->names('member.Role');

    /** 规则 资源接口集合 : member/rule */
    Route::resource('rule', '\App\Http\Controllers\Member\RuleController')->names('member.Rule');

    /** 规则组 资源接口集合 : member/ruleGroup */
    Route::resource('ruleGroup', '\App\Http\Controllers\Member\RuleGroupController')->names('member.RuleGroup');

    /** 用户角色 资源接口集合 : member/userRole */
    Route::resource('userRole', '\App\Http\Controllers\Member\UserRoleController')->names('member.UserRole');

    /** 权限 资源接口集合 : member/permission */
    Route::resource('permission', '\App\Http\Controllers\Member\PermissionController')->names('member.Permission');

    /** 导航菜单 资源接口集合 : member/leftMenu */
    Route::resource('leftMenu', '\App\Http\Controllers\Member\LeftMenuController')->names('member.LeftMenu');

    /** 角色菜单 资源接口集合 : member/roleMenu */
    Route::resource('roleMenu', '\App\Http\Controllers\Member\RoleMenuController')->names('member.RoleMenu');

    /** 个性化主题 资源接口集合 : member/personalTheme */
//	Route::resource('personalTheme','\App\Http\Controllers\Member\PersonalThemeController')->names('member.PersonalTheme');
    Route::get('personal/theme', '\App\Http\Controllers\Member\PersonalThemeController@getOnlineSetting');
    Route::patch('personal/theme', '\App\Http\Controllers\Member\PersonalThemeController@updateSetting');
});


Route::group(['prefix' => 'system'], function () {

    /* 根据code获取配置 :  system/systemConfig/code/{code} */
    Route::get('systemConfig/code/{code}', '\App\Http\Controllers\System\SystemConfigController@getConfigByCode')->name('system.systemConfig.getCode');
    /* 批量修改 : system/systemConfig/batchUpdate  */
    Route::patch('systemConfig/batchUpdate', '\App\Http\Controllers\System\SystemConfigController@batchUpdate')->name('system.systemConfig.batchUpdate');
    /** system_config 资源接口集合 : system/systemConfig */
    Route::resource('systemConfig', '\App\Http\Controllers\System\SystemConfigController')->names('system.config');

    /** 系统配置 资源接口集合 : system/systemConfig */
    Route::resource('systemConfig', '\App\Http\Controllers\System\SystemConfigController')->names('system.SystemConfig');

});


Route::group(['prefix' => 'cloud'], function () {

    /** 磁盘 资源接口集合 : cloud/disk */
    Route::resource('disk', '\App\Http\Controllers\Cloud\DiskController')->names('cloud.Disk');
    Route::get('disk/vendor/list', '\App\Http\Controllers\Cloud\DiskController@getVendorList')->name('cloud.disk.vendor.list');
    Route::get('disk/node/list', '\App\Http\Controllers\Cloud\DiskController@getNodeList')->name('cloud.disk.node.list');
    Route::post('disk/create/default', '\App\Http\Controllers\Cloud\DiskController@storeDefault')->name('cloud.disk.create.default');
    Route::post('disk/create/customer', '\App\Http\Controllers\Cloud\DiskController@storeCustomer')->name('cloud.disk.create.customer');
    Route::get('disk/all/online', '\App\Http\Controllers\Cloud\DiskController@getAllOnlineMemberDisk')->name('cloud.disk.all.online');

    Route::post('disk/{diskUid}/upload', '\App\Http\Controllers\Cloud\ResourceController@upload')->name('cloud.disk.upload');


    /** 资源分享 资源接口集合 : cloud/resourcesShare */
    Route::resource('resourcesShare', '\App\Http\Controllers\Cloud\ResourcesShareController')->names('cloud.ResourcesShare');

    Route::post('resource/create/{resourceUuid}/{diskUuid}', '\App\Http\Controllers\Cloud\ResourceController@createDirectory')->name('cloud.resource.create.resource.disk');
    Route::post('resource/create/{resourceUuid}', '\App\Http\Controllers\Cloud\ResourceController@createDirectory')->name('cloud.resource.create.resource');
    Route::patch('resource/rename/{resourceUid}', '\App\Http\Controllers\Cloud\ResourceController@uploadResourceName')->name('cloud.resource.rename.resource');

    Route::get('resource/desktop', '\App\Http\Controllers\Cloud\ResourceController@getDesktopResources')->name('cloud.resource.desktop');
    Route::get('resource/{diskUid}/{resourceUid}/list', '\App\Http\Controllers\Cloud\ResourceController@getDiskResourcesByResource')->name('cloud.disk.resource.list');
    Route::post('resource/{uuid}/upload', '\App\Http\Controllers\Cloud\ResourceController@uploadFile')->name('cloud.resource.upload');
    Route::post('resource/{uuid}/{diskUid}/upload', '\App\Http\Controllers\Cloud\ResourceController@uploadFile')->name('cloud.disk.resource.upload');
    Route::get('resource/{resourceUid}/list', '\App\Http\Controllers\Cloud\ResourceController@getResourceList')->name('cloud.resource.all.list');
    /** 资源 资源接口集合 : cloud/resource */
    Route::resource('resource', '\App\Http\Controllers\Cloud\ResourceController')->names('cloud.Resource');


});


Route::group(['prefix' => 'tool'], function () {

    Route::post('upload/image/cache', '\App\Http\Controllers\ToolController@uploadCacheImage');

});
