<?php
return [
    'db'           => require_once APP_PATH . '/config/database.php',
    'open_modules' => false,
    'aliyun'     => require_once APP_PATH . '/config/aliyun.php',
    'csrf' => false,
    'redis'          => [
        'host'     => '127.0.0.1',
        'port'     => '6379',
        'password' => '123456', //密码 默认为空
        'prefix'   => 'novel_', //KEY的前缀 默认 空
    ],
];