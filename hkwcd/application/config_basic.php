<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/11
 * Time: 15:43
 */

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------
    // 默认Host地址
    'app_host'               => '',
    // 应用调试模式
    'app_debug'              => false,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态

    // 是否开启多语言
    'lang_switch_on'         => true,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',
    // 默认语言
//    'default_lang'           => 'zh-cn',


    // +----------------------------------------------------------------------
    // | 数据库设置
    // +----------------------------------------------------------------------

    'database'               => [
        // 数据库类型
        'type'            => 'mysql',
        // 数据库连接DSN配置
        'dsn'             => '',
        // 服务器地址
        'hostname'        => '127.0.0.1',
        // 数据库名
        'database'        => 'hkwcd',
        // 数据库用户名
        'username'        => 'root',
        // 数据库密码
        'password'        => '',
        // 数据库连接端口
        'hostport'        => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => '',
        // 数据库调试模式
        'debug'           => false,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy'          => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate'     => false,
        // 读写分离后 主服务器数量
        'master_num'      => 1,
        // 指定从服务器序号
        'slave_no'        => '',
        // 是否严格检查字段是否存在
        'fields_strict'   => true,
        // 数据集返回类型
        'resultset_type'  => 'array',
        // 自动写入时间戳字段
        'auto_timestamp'  => false,
        // 时间字段取出后的默认时间格式
        'datetime_format' => 'Y-m-d H:i:s',
        // 是否需要进行SQL性能分析
        'sql_explain'     => false,
    ],


    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => '.html',
        // 预先加载的标签库
        'taglib_pre_load'     =>    'app\index\taglib\Yang',

    ],

    "web_copyright" => "Copyright @ 2012-2020 hkwcd.com All Rights Reserved.site map香港华度货运有限公司",


    //验证码
    "captcha"        => [
        'codeSet'  => '23456789',
        'useNoise' => false,
        "useCurve" => false,
        "length"   => 4,
        //		"fontSize" => 100,
        //		"fontttf"  => "2.ttf",
    ],
];