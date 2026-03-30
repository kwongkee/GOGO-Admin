<?php

return [
    // 日志记录方式，支持 file、socket、test 等，这里使用文件记录
    'type'  => 'File',
    // 日志保存目录
    'path'  => APP_PATH .'../runtime/'. 'log' . DS,
    // 单文件日志写入
    'single'  => false,
    // 独立日志级别
    'apart_level'   => [],
    // 最大日志文件数量
    'max_files'  => 0,
    // 日志记录级别
    'level'   => ['info','warning','error'],
];