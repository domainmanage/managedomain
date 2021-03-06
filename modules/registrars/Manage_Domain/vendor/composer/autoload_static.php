<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita2063c1f5f4b60e95ae8092a1a0591ef
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'ManageDomainLibs\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ManageDomainLibs\\' => 
        array (
            0 => __DIR__ . '/../..' . '/libs',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita2063c1f5f4b60e95ae8092a1a0591ef::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita2063c1f5f4b60e95ae8092a1a0591ef::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
