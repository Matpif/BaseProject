<?php

namespace App;

use App\libs\App\VarientObject;
use Doctrine\Common\Cache\RedisCache;
use Redis;

class Cache extends VarientObject
{


    private static $_instance;

    /**
     * @var RedisCache
     */
    private $_cacheRedis;

    /**
     * Cache constructor.
     */
    public function __construct()
    {
        if (App::getInstance()->cacheIsEnabled()) {
            $redis = new Redis();
            $redis->connect(Config::getInstance()->getAttribute('redis', 'host'),
                Config::getInstance()->getAttribute('redis', 'port'));
            $this->_cacheRedis = new RedisCache();
            $this->_cacheRedis->setRedis($redis);
        }
    }

    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new Cache();
        }

        return self::$_instance;
    }

    /**
     * @param $key
     * @return bool
     */
    public function cacheExist($key)
    {
        if (App::getInstance()->cacheIsEnabled()) {
            return $this->_cacheRedis->contains($key);
        }

        return false;
    }

    /**
     * @return RedisCache
     */
    public function getCacheRedis()
    {
        return $this->_cacheRedis;
    }
}