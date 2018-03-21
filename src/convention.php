<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2016/12/12
 * Time: 23:33
 */
return [
    'response'            => [
        'default_code'   => 'SUCCESS',
        'default_msg'    => 'SUCCESS',
        'default_errno'  => 'SYSTEM_ERROR',
        'default_errmsg' => '系统异常，请稍后重试',
        'header'         => [
            'Request-Id' => REQUEST_ID,
        ],
        'options'        => [
        ],
    ],
    //路由配置
    'dispatcher'          => [
        'layer'          => 's',
        'default_action' => 'index',
        'namespace'      => 'app',
    ],
    'error_reportiong'    => E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING,
    //不需要记录的异常
    'no_logged_exception' => [
        \worms\api\ApiException::class,
    ],
    'cache'               => [
        'type'          => 'memached',
        'expire'        => 0,
        'cache_subdir'  => false,
        'prefix'        => 'xzb_',
        'path'          => VPATH,
        'data_compress' => false,
    ],
    'log'                 => [
        //开关
        'status'      => true,
        //时间格式
        'time_format' => ' c ',
        //日志未知
        'filepath'    => VPATH . '/log.txt',
        //日志记录类型
        'level'       => ['log', 'error', 'info', 'sql', 'notice', 'alert'],
        //是否转码gbk
        'togbk'       => true,
        'suffix'      => REQUEST_ID,
    ],
];