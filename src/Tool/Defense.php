<?php
/**
 * filename: Defense.php.
 * author: china.php@qq.com
 * datetime: 2020/9/9
 */

namespace tangyong\library\Tool;


use tangyong\library\Core\RedisFactory;
use tangyong\library\Exception\TSdkException;

class Defense
{
    //次数
    const F_COUNT = 5;
    //间隔时长
    const F_INTERVAL = 60;

    const LIMIT_COUNT = 10;
    const LIMIT_COUNT_TIME = 24 * 3600;

    static $instance = null;

    private $redis = null;


    private function __construct($config)
    {
        $this->redis = RedisFactory::create($config);
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public static function getInstance(array $config)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**限频
     * @param $key
     * @throws TSdkException
     */
    public function limitFrequency($key)
    {
        $key = "limit-frequency-" . "-" . md5($key);
        $num = $this->redis->lLen($key);
        if ($num >= self::F_COUNT) {
            $last_time = $this->redis->lIndex($key, -1);
            if (time() - $last_time < self::F_INTERVAL) {
                throw new TSdkException("访问频率超过了限制，请稍后再试");
            } else {
                $this->redis->lPush($key, time());
                $this->redis->lTrim($key, 0, self::F_COUNT - 1);
            }
        } else {
            $this->redis->lPush($key, time());
        }
    }

    /** 限次
     * @param $key
     * @throws TSdkException
     */
    public function limitCount($key)
    {
        $key = "limit-count-" . md5($key);
        $result = $this->redis->get($key);
        if (!empty($result)) {
            if ($result >= self::LIMIT_COUNT) {
                throw new TSdkException("访问次数超过了限制，请隔天再试");
            }
        }
        $o_result = $this->redis->set($key, 1, ["nx", 'ex' => self::LIMIT_COUNT_TIME]);
        if (!$o_result) {
            $this->redis->incr($key);
        }
    }
}