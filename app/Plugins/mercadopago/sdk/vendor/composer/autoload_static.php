<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit608c58bf98790af3b2f95ff7a4a1826a
{
    public static $classMap = array (
        'MP' => __DIR__ . '/../..' . '/lib/mercadopago.php',
        'MPRestClient' => __DIR__ . '/../..' . '/lib/mercadopago.php',
        'MercadoPagoException' => __DIR__ . '/../..' . '/lib/mercadopago.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit608c58bf98790af3b2f95ff7a4a1826a::$classMap;

        }, null, ClassLoader::class);
    }
}
