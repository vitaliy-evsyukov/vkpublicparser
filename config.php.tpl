<?php

$config = array(
    'services' => array(
        'login'  => array(
            'class'     => '\\components\\interaction\\Login',
            'login'     => 'username',
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
            'cookieJar' => 'cookie.txt',
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