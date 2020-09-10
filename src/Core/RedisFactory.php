<?php
/**
 * filename: Tredis.php.
 * author: china.php@qq.com
 * datetime: 2020/9/9
 */

namespace tangyong\library\Core;


use tangyong\library\Exception\TSdkException;

class RedisFactory
{
    private static $redis = null;

    /**
     * @param array $config
     * @return \Redis|null
     * @throws TSdkException
     */
    public static function create(array $config)
    {
        try {
            self::$redis = new \Redis();
            self::$redis->connect($config['hostname'], $config['port'], $config['timeout'] ?? 0.0);
            //是否需要密码
            if (!empty($config['password'])) {
                self::$redis->auth($config['password']);
            }
            //选库
            self::$redis->select($config['database']);
        } catch (\Exception $e) {
            throw new TSdkException("redis连接失败");
        }
        if (!empty(self::$redis)) {
            return self::$redis;
        } else {
            throw new TSdkException("redis连接失败");
        }
    }
}