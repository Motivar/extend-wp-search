<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit76d963423bfeec46cd12099c48bde84b
{
    public static $files = array (
        '071d904d7e9f67639b5e5bc65c2988dc' => __DIR__ . '/../..' . '/includes/functions/init.php',
    );

    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'EWP_Search\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'EWP_Search\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit76d963423bfeec46cd12099c48bde84b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit76d963423bfeec46cd12099c48bde84b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit76d963423bfeec46cd12099c48bde84b::$classMap;

        }, null, ClassLoader::class);
    }
}