<?php

namespace BaseProject\Cms\Block;

use App\App;
use App\Cache;
use App\libs\App\CollectionDb;

class Block extends \App\libs\App\Block
{
    public function getHtml()
    {
        $returned = '';
        if ($this->getName()) {
            $this->setKey(['/cms/' . $this->getName(), App::getInstance()->getLanguageCode()]);
            if (App::getInstance()->cacheIsEnabled() && $this->_useCache) {
                if (Cache::getInstance()->getCacheRedis()->contains($this->_key)) {
                    $returned = Cache::getInstance()->getCacheRedis()->fetch($this->_key);

                    return $returned;
                }
            }
            $blockCms = CollectionDb::getInstanceOf('Cms_Block')->load([
                'name' => $this->getName(),
                'is_enabled' => 1,
                'language_code' => App::getInstance()->getLanguageCode()
            ])->getFirstRow();
            if ($blockCms) {
                $returned = $blockCms->getContent(true);
                if (App::getInstance()->cacheIsEnabled() && $this->_useCache) {
                    Cache::getInstance()->getCacheRedis()->save($this->_key, $returned);
                }
            }
        }

        return $returned;
    }
}