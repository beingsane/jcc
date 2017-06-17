<?php
namespace JCC;

class Loader {

    protected static function autoload()
    {
        $base = __DIR__;
        $vendor = $base;
        $autoload = "{$vendor}/vendor/autoload.php";
        $loader = require_once $autoload;
        $loader->addPsr4('JCC\\', "{$base}/classes/JCC");
        return $loader;
    }
    
    public static function get() {
        static $loader;
        if (!$loader) {
            $loader = self::autoload();
        }
        return $loader;
    }

    public static function setup() {
        self::get();
    }
}
