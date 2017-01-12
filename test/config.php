<?php
use ffan\php\utils\Config as FFanConfig;

FFanConfig::addArray(
    array(
        'ffan-mysql:main' => array(
            'host' => '127.0.0.1',
            'user' => 'test',
            'password' => '12345678',
            'database' => 'test_db',
        ),
        'ffan-mysql:rw' => array(
            'master' => array(
                'host' => '127.0.0.1',
                'user' => 'test',
                'password' => '12345678',
                'database' => 'test_db'
            ),
            'slave' => array(
                'host' => '127.0.0.1',
                'user' => 'test',
                'password' => '12345678',
                'database' => 'test_db'
            )
        ),
        'ffan-logger:web' => array(
            'file' => 'test',
            'path' => 'test'
        ),
        'runtime_path' => __DIR__ . DIRECTORY_SEPARATOR,
        'env' => 'dev'
    )
);
