<?php
use UiStd\Common\Config as UisConfig;

UisConfig::addArray(
    array(
        'uis-mysql:main' => array(
            'host' => '127.0.0.1',
            'user' => 'test',
            'password' => '12345678',
            'database' => 'test_db',
        ),
        'uis-mysql:rw' => array(
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
        'runtime_path' => __DIR__ . DIRECTORY_SEPARATOR,
        'env' => 'dev'
    )
);
