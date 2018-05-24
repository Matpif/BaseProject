<?php

namespace BaseProject\Rewrite\Model;

use App\libs\App\ModelDb;

/**
 * Class Rewrite
 * @package BaseProject\Rewrite\Model
 *
 * @method int getId()
 * @method string getName()
 * @method string getBasicUrl()
 * @method string getRewriteUrl()
 * @method bool getRedirectVisible()
 * @method setId(int)
 * @method setName(string)
 * @method setBasicUrl(string)
 * @method setRewriteUrl(string)
 * @method setRedirectVisible(bool)
 *
 * @dbField id INT AUTO_INCREMENT PRIMARY KEY
 * @dbField name varchar(50) not null
 * @dbField basic_url varchar(500) not null
 * @dbField rewrite_url varchar(500) not null
 * @dbField redirect_visible tinyint(1) not null default 0
 *
 */
class Rewrite extends ModelDb
{
    CONST REDIRECT_NOT_VISIBLE = 0;
    CONST REDIRECT_VISIBLE_PERMANENTLY = 1;
    CONST REDIRECT_VISIBLE_TEMPORARY = 2;


}