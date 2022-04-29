<?php


namespace App\Core\Enums;


use xiaolin\Enum\Enum;

/**
 * ErrorCode
 * @method static getMessage(string $value)
 * @date : 2022/4/24 18:46
 * @author : 孤鸿渺影
 */
class ErrorCode extends Enum
{
    /**
     * @Message('Success')
     */
    public static $ENUM_SUCCESS = 0;
    /**
     * @Message('参数错误')
     */
    public static $ENUM_PARAM_ERROR = 10001;
    /**
     * @Message('数据验证失败')
     */
    public static $ENUM_PARAM_VALIDATE_ERROR = 10002;
    /**
     * @Message('数据不存在')
     */
    public static $ENUM_NO_DATA_ERROR = 10003;
    /**
     * @Message('未知错误')
     */
    public static $ENUM_UNKNOWN_ERROR = 10004;
    /**
     * @Message('系统错误')
     */
    public static $ENUM_SYSTEM_ERROR = 10005;
    /**
     * @Message('参数不可为空')
     */
    public static $ENUM_PARAM_NULL_ERROR = 10006;
    /**
     * @Message('账号或密码错误')
     */
    public static $ENUM_ACCOUNT_PASSWORD_ERROR = 10007;
    /**
     * @Message('上传失败')
     */
    public static $ENUM_UPLOAD_ERROR = 10010;
    /**
     * @Message('无接口访问权限')
     */
    public static $ENUM_API_NO_AUTH_ERROR = 10107;





    /**
     * @Message('操作失败')
     */
    public static $ENUM_ACTION_ERROR = 30001;
    /**
     * @Message('写入失败')
     */
    public static $ENUM_WRITE_ERROR = 30002;
    /**
     * @Message('更新失败')
     */
    public static $ENUM_UPDATE_ERROR = 30003;
    /**
     * @Message('删除失败')
     */
    public static $ENUM_DELETE_ERROR = 30004;
    /**
     * @Message('查询失败')
     */
    public static $ENUM_SELECT_ERROR = 30005;



    // 会话类

    /**
     * 会话过期（登录状态过期）
     * @Message('登录状态过期')
     */
    public static $ENUM_LOGIN_OVERDUE_ERROR = 40002;
    /**
     * 无效的授权码或临时凭证
     * @Message('无效的授权码')
     */
    public static $ENUM_AUTHORIZATION_CODE_ERROR = 40003;
    /**
     * 无效的访问令牌或最终凭证
     * @Message('无效的访问令牌')
     */
    public static $ENUM_AUTHORIZATION_TOKEN_ERROR = 40003;

    /**
     * @Message('无效Token')
     */
    public static $ENUM_TOKEN_ERROR = 40007;
    /**
     * @Message('无效Token，格式错误')
     */
    public static $ENUM_TOKEN_FORMAT_ERROR = 40009;
    /**
     * @Message('无效Token，解密失败')
     */
    public static $ENUM_TOKEN_DECRYPT_ERROR = 40010;


    // 权限类

    /**
     * @Message('未登录')
     */
    public static $ENUM_NO_LOGIN_ERROR = 50001;
    /**
     * @Message('账号异常')
     */
    public static $ENUM_ACCOUNT_EXCEPTION = 50002;

}
