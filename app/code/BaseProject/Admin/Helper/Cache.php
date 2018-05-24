<?php

namespace BaseProject\Admin\Helper;

use App\App;
use App\Config;
use App\libs\App\Helper;
use File_Gettext;
use Redis;

class Cache extends Helper
{

    public function clearCache()
    {
        $filePath = App::PathRoot() . '/var/cache/config.json';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $cssMin = App::PathRoot() . '/assets/css/min';
        if (file_exists($cssMin)) {
            $this->delete_directory($cssMin);
        }

        $jsMin = App::PathRoot() . '/assets/js/min';
        if (file_exists($jsMin)) {
            $this->delete_directory($jsMin);
        }

        if (App::getInstance()->cacheIsEnabled()) {
            $redis = new Redis();
            $redis->connect(Config::getInstance()->getAttribute('redis', 'host'),
                Config::getInstance()->getAttribute('redis', 'port'));
            $redis->flushAll();
        }

        $opCacheStatus = opcache_get_status(false);
        if (isset($opCacheStatus['opcache_enabled']) && $opCacheStatus['opcache_enabled'] == true) {
            opcache_reset();
        }

        clearstatcache();
    }

    private function delete_directory($dirname) {
        if (is_dir($dirname))
            $dir_handle = opendir($dirname);
        if (!$dir_handle)
            return false;
        while($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file))
                    unlink($dirname."/".$file);
                else
                    delete_directory($dirname.'/'.$file);
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }

    public function clearCacheTranslate()
    {
        App::getInstance()->getTranslate()->removeCache();
    }
}