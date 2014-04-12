<?php

$config = array(
    // уровень вывода отладочной информации, от 0 до 3
    'verbosity'  => 3,
    // время ожидания перед следующим запросов, от 2 секунд
    'maxTimeout' => 3,
    'services'   => array(
        'login'  => array(
            'class'     => '\\components\\interaction\\Login',
            // логин
            'login'     => 'username',
            // пароль
            'password'  => 'password',
            'transport' => 'services.curl'
        ),
        'groups' => array(
            'subscribers' => array(
                'class'     => '\\components\\interaction\\groups\\Subscribers',
                'transport' => 'services.curl',
                'cache'     => 'services.cache'
            )
        ),
        'curl'   => array(
            'class'     => '\\components\\interaction\\Curl',
            'cookieJar' => __DIR__ . DS . 'cookie.txt',
            'userAgent' => 'Opera/9.80 (Android; Opera Mini/7.5.33361/31.1350; U; en) Presto/2.8.119 Version/11.11'
        ),
        'cache'  => array(
            'class'     => '\\components\\interaction\\Cache',
            'cacheDir'  => __DIR__ . DS . 'cache',
            'prefixLen' => 5,
            'lifetime'  => 1200
        )
    )
);