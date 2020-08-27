<?php
/**
 * filename: ConsistentHash.php.
 * author: china.php@qq.com
 * datetime: 2020/8/25
 */

namespace tangyong\library\Core;

use tangyong\library\Exception\TSdkException;

class ConsistentHash
{
    /*
     $config = [
          [
           'host' => '127.0.0.1',
           'port' => 6379,
           'auth' => '',
           'database'=>2,
           'timeout'=>3,
          ],
         [
           'host' => '127.0.0.1',
           'port' => 6379,
           'auth' => '',
           'database'=>2
           'timeout'=>3,
          ]
          ...
      ];
     */
    private static $config = [];//使用前需按上格式完成初始化
    //节点对应的散列值
    private $nodes = [];
    //散列值对应节点
    private $positions = [];
    private $virtualNum = 64;
    private static $instance;

    /** 初始化节点
     * ConsistentHash constructor.
     * @throws SdkException
     */
    private function __construct()
    {
        foreach (self::$config as $key => $value) {
            if (!isset($value['host'])) {
                throw new TSdkException("ConsistentHash类配置有误");
            }
            $this->addNode($value['host']);
        }
    }

    /** 初始化
     * @param $config
     * @return ConsistentHash
     * @throws SdkException
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**  添加节点
     * @param $node
     */
    private function addNode($node)
    {
        //生成64个虚拟节点
        for ($i = 0; $i < $this->virtualNum; $i++) {
            $newNode = $node . "-" . $i;
            $hash = $this->getHash($newNode);
            $this->positions[$hash] = $node;
            $this->nodes[$node][] = $hash;
        }
        $this->sortPos();
    }

    /** 删除节点
     * @param $node
     * @return bool
     */
    private function delNodel($node)
    {
        if (!isset($this->nodes[$node])) {
            return false;
        }
        foreach ($this->nodes[$node] as $key => $value) {
            foreach ($this->positions as $k => $v) {
                if ($value == $k) {
                    unset($this->positions[$k]);
                    break;
                }
            }
        }
        unset($this->nodes[$node]);
        $this->sortPos();
    }

    /**
     * @param $str
     * @param bool $debug
     * @return bool|\Redis
     */
    public function lookUp($str, $debug=false)
    {
        $hash = $this->getHash($str);
        $point = null;
        foreach ($this->positions as $key => $value) {
            if ($hash <= $key) {
                $point = $value;
                break;
            }
        }
        if (empty($point)) {
            $point = end($this->positions);
        }
        if ($debug) {
            echo "[";
            foreach ($this->positions as $key => $value) {
                echo $key . "=>" . $value . "<br/>";
            }
            echo "]";
            echo "<hr/>[{$str}的哈希值" . $hash . "被路由到][" . $point . " 结点]";
        }
        if (!empty($point)) {
            foreach (self::$config as $value) {
                if (strcasecmp($value['host'], $point) === 0) {
                    $Redis = new \Redis();
                    $Redis->connect($value['host'], $value['port'], $value['timeout']);
                    //是否需要密码
                    if (!empty($value['auth'])) {
                        $Redis->auth($value['auth']);
                    }
                    //选库
                    $Redis->select($value['database']);
                    return $Redis;
                }
            }
        }
        return false;
    }

    /**
     *  生成hash值
     * @param $str
     * @return string
     */
    private function getHash($str)
    {
        return sprintf("%u", crc32($str));
    }

    /**
     *  排序位置
     */
    private function sortPos()
    {
        ksort($this->positions, SORT_REGULAR);
    }


    private function __clone()
    {
    }
}