<?php

namespace App\Utilities;

class CacheUtility
{
    protected static $cache_file;
    protected static $cache_enabled = CACHE_ENABLED;
    const EXPIRE_TIME = 3600;

    public static function init()
    {
        self::$cache_file = CACHE_DIR . '/' . md5($_SERVER['REQUEST_URI']) . ".json";

        if ($_SERVER['REQUEST_METHOD'] != 'GET')
            self::$cache_enabled = 0;
    }

    public static function cache_exits()
    {
        self::init();
        return (file_exists(self::$cache_file) && (time() - self::EXPIRE_TIME) < filemtime(self::$cache_file));
    }

    public static function start()
    {
        self::init();

        if (!self::$cache_enabled) {
            return;
        }

        if (self::cache_exits()) {
            Response::setHeaders();
            readfile(self::$cache_file);
            exit;
        }

        ob_start();
    }

    public static function end()
    {
        if (!self::$cache_enabled) {
            return;
        }

        $cachedfile = fopen(self::$cache_file, 'w');

        fwrite($cachedfile, ob_get_contents());
        fclose($cachedfile);

        ob_end_flush();
    }

    public static function flush()
    {
        $files = glob(CACHE_DIR . "*");

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}