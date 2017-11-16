<?php

namespace BaseProject\Admin\Helper;

use App\App;
use App\Config;
use App\libs\App\Helper;
use File_Gettext;
use Redis;

class Cache extends Helper {

    public function clearCache() {
        $filePath = App::PathRoot() . '/var/cache/config.json';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if (App::getInstance()->cacheIsEnabled()) {
            $redis = new Redis();
            $redis->connect(Config::getInstance()->getAttribute('redis', 'host'),
                Config::getInstance()->getAttribute('redis', 'port'));
            $redis->flushAll();
        }

        clearstatcache();
    }

    /**
     *
     */
    public function clearCacheTranslate() {
        $languages = scandir(App::PathRoot() . '/locale/');

        if (file_exists(App::PathRoot() . '/var/translate')) {
            system("rm -rf " . escapeshellarg(App::PathRoot() . '/var/translate'));
        }

        foreach ($languages as $language) {
            if ($language == '.' || $language == '..') {
                continue;
            }

            $strings = [];
            $csvFiles = scandir(App::PathRoot() . '/locale/' . $language);
            foreach ($csvFiles as $csvFile) {
                if ($csvFile == '.' || $csvFile == '..') {
                    continue;
                }
                $tanslateModule = [];

                $handle = fopen(App::PathRoot() . '/locale/' . $language . '/' . $csvFile, "r");
                $module = substr($csvFile, 0, strpos($csvFile, '.csv'));

                while (($data = fgetcsv($handle)) !== false) {
                    $s = [$data[0] => $data[1]];
                    $strings = array_merge($strings, $s);
                    $tanslateModule = array_merge($tanslateModule, $s);
                }

                $getText = new File_Gettext();
                $getText->strings = $tanslateModule;
                $getText->meta = [
                    "Project-Id-Version" => App::getInstance()->getAppName() . ' ' . App::getInstance()->getAppVersion() . '\n',
                    "POT-Creation-Date" => ' ' . date('c') . '\n',
                    "PO-Revision-Date" => ' ' . date('c') . '\n',
                    "MIME-Version" => ' 1.0\n',
                    "Content-Type" => ' text/plain; charset=UTF-8\n',
                    "Content-Transfer-Encoding" => ' 8bit\n',
                    "Plural-Forms" => ' nplurals=2; plural=n>1;\n'
                ];
                $mo = $getText->toMO();
//                $po = $getText->toPO();
                if (!file_exists(App::PathRoot() . "/var/translate")) {
                    mkdir(App::PathRoot() . "/var/translate");
                }
                if (!file_exists(App::PathRoot() . "/var/translate/{$language}")) {
                    mkdir(App::PathRoot() . "/var/translate/{$language}");
                }
                if (!file_exists(App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES")) {
                    mkdir(App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES");
                }
//                $po->file = App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES/{$module}.po";
//                $po->save();
                $mo->file = App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES/{$module}.mo";
                $mo->save();
            }

            $getText = new File_Gettext();
            $getText->strings = $strings;
            $getText->meta = [
                "Project-Id-Version" => App::getInstance()->getAppName() . ' ' . App::getInstance()->getAppVersion() . '\n',
                "POT-Creation-Date" => ' ' . date('c') . '\n',
                "PO-Revision-Date" => ' ' . date('c') . '\n',
                "MIME-Version" => ' 1.0\n',
                "Content-Type" => ' text/plain; charset=UTF-8\n',
                "Content-Transfer-Encoding" => ' 8bit\n',
                "Plural-Forms" => ' nplurals=2; plural=n>1;\n'
            ];
            $mo = $getText->toMO();
            if (!file_exists(App::PathRoot() . "/var/translate")) {
                mkdir(App::PathRoot() . "/var/translate");
            }
            if (!file_exists(App::PathRoot() . "/var/translate/{$language}")) {
                mkdir(App::PathRoot() . "/var/translate/{$language}");
            }
            if (!file_exists(App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES")) {
                mkdir(App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES");
            }
            $mo->file = App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES/app.mo";
            $mo->save();
        }
    }
}