<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit10f172dbaabfdcdf12e0d31dd1f34f74
{
    public static $files = array (
        '7b11c4dc42b3b3023073cb14e519683c' => __DIR__ . '/..' . '/ralouphie/getallheaders/src/getallheaders.php',
        'c964ee0ededf28c96ebd9db5099ef911' => __DIR__ . '/..' . '/guzzlehttp/promises/src/functions_include.php',
        'a0edc8309cc5e1d60e3047b5df6b7053' => __DIR__ . '/..' . '/guzzlehttp/psr7/src/functions_include.php',
        '37a3dc5111fe8f707ab4c132ef1dbc63' => __DIR__ . '/..' . '/guzzlehttp/guzzle/src/functions_include.php',
        'decc78cc4436b1292c6c0d151b19445c' => __DIR__ . '/..' . '/phpseclib/phpseclib/phpseclib/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'p' =>
        array (
            'phpseclib\\' => 10,
        ),
        'V' =>
        array (
            'Verifone\\' => 9,
        ),
        'P' =>
        array (
            'Psr\\Http\\Message\\' => 17,
        ),
        'L' =>
        array (
            'Lamia\\' => 6,
        ),
        'G' =>
        array (
            'GuzzleHttp6\\Psr7\\' => 16,
            'GuzzleHttp6\\Promise\\' => 19,
            'GuzzleHttp6\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'phpseclib\\' =>
        array (
            0 => __DIR__ . '/..' . '/phpseclib/phpseclib/phpseclib',
        ),
        'Verifone\\' =>
        array (
            0 => __DIR__ . '/..' . '/verifone/core/Verifone',
        ),
        'Psr\\Http\\Message\\' =>
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Lamia\\' =>
        array (
            0 => __DIR__ . '/..' . '/lamiaoy/validation/Lamia',
        ),
        'GuzzleHttp6\\Psr7\\' =>
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/psr7/src',
        ),
        'GuzzleHttp6\\Promise\\' =>
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/promises/src',
        ),
        'GuzzleHttp6\\' =>
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'L' =>
        array (
            'Lamia\\HttpClient\\' =>
            array (
                0 => __DIR__ . '/..' . '/lamiaoy/httpclient/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit10f172dbaabfdcdf12e0d31dd1f34f74::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit10f172dbaabfdcdf12e0d31dd1f34f74::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit10f172dbaabfdcdf12e0d31dd1f34f74::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
