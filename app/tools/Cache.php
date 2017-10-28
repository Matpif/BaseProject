<?php

namespace App;

use App\libs\App\VarientObject;
use Redis;

class Cache extends VarientObject
{


    private static $_instance;

    /**
     * @var \Doctrine\Common\Cache\RedisCache
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
            $this->_cacheRedis = new \Doctrine\Common\Cache\RedisCache();
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
     * @return \Doctrine\Common\Cache\RedisCache
     */
    public function getCacheRedis()
    {
        return $this->_cacheRedis;
    }
}