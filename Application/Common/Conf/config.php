<?php
return array(
    /* 模块相关配置 */
    'DEFAULT_MODULE'        => 'Home',
    'MODULE_DENY_LIST'      => array('Common', 'User'),
    'MODULE_ALLOW_LIST'     => array('Home', 'Admin', 'Api'),

    'DEFAULT_LANG'          => 'en-us', // 默认语言
    
    'WEB_SITE_CLOSE'        => false,
    'USER_ALLOW_REGISTER'   => true,
    /* URL伪静态后缀设置 */
    'URL_HTML_SUFFIX'       => '',

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY'     => 'fhDo{4b7&mJX^>+6riCvtUS/"19MAZqp(;)5c8ul', //默认数据加密KEY

    /* 调试配置 */
    'SHOW_PAGE_TRACE'   => false,

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    /* 数据库配置 */
    'DB_TYPE'   => 'mongo', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'onelibrary', // 数据库名
    'DB_USER'   => 'onelibrary', // 用户名
    'DB_PWD'    => 'onelibrary',  // 密码
    'DB_PORT'   => '27017', // 端口
    'DB_PREFIX' => 't_', // 数据库表前缀
    'DB_CHARSET'=> 'utf8',

    'DEFAULT_THEME'     => 'default',
    'APP_USE_NAMESPACE' => true,
    //扩展函数库
    "LOAD_EXT_FILE"     => '',
    'LOAD_EXT_CONFIG'   => 'onelibrary',

    'LOG_RECORD'            =>  false,   // 默认不记录日志
    'LOG_TYPE'              =>  'File', // 日志记录类型 默认为文件方式
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
    'LOG_EXCEPTION_RECORD'  =>  false,    // 是否记录异常信息日志
);