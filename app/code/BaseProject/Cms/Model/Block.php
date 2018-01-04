<?php

namespace BaseProject\Cms\Model;

use App\libs\App\ModelDb;

/**
 * @method int getId()
 * @method String getName()
 * @method String getLanguageCode()
 * @method String getTitle()
 * @method Boolean getActivePageFormat()
 * @method Boolean getIsEnabled()
 * @method setId(int $id)
 * @method setName(String $name)
 * @method setLanguageCode(String $languageCode)
 * @method setTitle(String $title)
 * @method setContent(String $content)
 * @method setActivePageFormat(Boolean $activePageFormat)
 * @method setIsEnabled(Boolean $isEnabled)
 *
 * @dbField id INT PRIMARY KEY AUTO_INCREMENT
 * @dbField name NVARCHAR(25) NOT NULL
 * @dbField language_code NVARCHAR(5) NULL
 * @dbField title NVARCHAR(50) NULL
 * @dbField content TEXT NULL
 * @dbField active_page_format BOOLEAN DEFAULT 0
 * @dbField is_enabled BOOLEAN DEFAULT 1
 *
 * class DefaultCms_BlockModel
 */
class Block extends ModelDb
{
    const PATTERN_TAG = '/{block (.*)}/';
    const PATTERN_ATTRIBUTES = '/(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?/';

    public function getContent($withBlock = true) {
        if ($withBlock) {
            /*
             * Find {block type="code|cms" id="id_block"} in content
             */
            $content = $this->getAttribute('content');
            if (preg_match_all(self::PATTERN_TAG, $content, $tags)) {
                foreach ($tags[1] as $key => $tag) {
                    preg_match_all(self::PATTERN_ATTRIBUTES, $tag, $attributes);

                    $type = null;
                    $id = null;
                    if (isset($attributes[1][0])) {
                        switch ($attributes[1][0]) {
                            case 'type':
                                $type = $attributes[2][0];
                                break;
                            case 'id':
                                $id  = $attributes[2][0];
                                break;
                        }
                    }

                    if (isset($attributes[1][1])) {
                        switch ($attributes[1][1]) {
                            case 'type':
                                $type = $attributes[2][1];
                                break;
                            case 'id':
                                $id  = $attributes[2][1];
                                break;
                        }
                    }

                    switch ($type) {
                        case 'code':
                            $block = \App\libs\App\Block::getBlock($id);
                            if ($block) {
                                $content = str_replace($tags[0][$key], $block->getHtml(), $content);
                            }
                            break;
                        case 'block':
                            $block = \App\libs\App\Block::getBlock('Cms_Block');
                            $block->setName($id);
                            $content = str_replace($tags[0][$key], $block->getHtml(), $content);
                            break;
                    }
                }
            }
            return $content;
        } else {
            return $this->getAttribute('content');
        }
    }

}