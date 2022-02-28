<?php


class RedisSentinelPool
{
    /**
     * @var Sentinel[]
     */
    protected $sentinels = [];

    public function __construct($sentinels = [])
    {
        foreach ($sentinels as $sentinel) {
            $this->addSentinel($sentinel['host'], $sentinel['port']);
        }
    }

    public function addSentinel($host, $port)
    {
        $sentinel = new RedisSentinel();
        // 如果连接没问题 添加到数组中
        if ($sentinel->connect($host, $port)) {
            $this->sentinels[] = $sentinel;
            return true;
        }

        return false;
    }

    /**
     * 获取redis对象
     * @param  [type] $masterName [description]
     * @return [type]             [description]
     */
    public function getRedis($masterName)
    {
        $address = $this->getMasterAddrByName($masterName);
        $redis = new \Redis();
        if (!$redis->connect($address['ip'], $address['port'])) {
            throw new \RedisException("connect to redis failed");
        }

        return $redis;
    }

    public function __call($name, $arguments)
    {
        foreach ($this->sentinels as $sentinel) {
            // 判断 RedisSentinel 中是否有对应的方法
            if (!method_exists($sentinel, $name)) {
                throw new \BadMethodCallException("method not exists. method: {$name}");
            }
            try {
                // 调用 RedisSentinel 中对应的方法
                return call_user_func_array(array($sentinel, $name), $arguments);
            } catch (\Exception $e) {
                continue;
            }
        }

        // 所有哨兵 都下线
        throw new \RedisException("all sentinel failed");
    }
}
