<?php

/*
    phpredis 5.2.0 开始 扩展中有RedisSentinel类

    https://github.com/phpredis/phpredis/blob/develop/sentinel.markdown#readme
    https://redis.io/topics/sentinel


    Sentinel commands
    The following is a list of accepted commands, not covering commands used in order to modify the Sentinel configuration, which are covered later.

    PING This command simply returns PONG.
    SENTINEL masters Show a list of monitored masters and their state.
    SENTINEL master <master name> Show the state and info of the specified master.
    SENTINEL replicas <master name> Show a list of replicas for this master, and their state.
    SENTINEL sentinels <master name> Show a list of sentinel instances for this master, and their state.
    SENTINEL get-master-addr-by-name <master name> Return the ip and port number of the master with that name. If a failover is in progress or terminated successfully for this master it returns the address and port of the promoted replica.
    SENTINEL reset <pattern> This command will reset all the masters with matching name. The pattern argument is a glob-style pattern. The reset process clears any previous state in a master (including a failover in progress), and removes every replica and sentinel already discovered and associated with the master.
    SENTINEL failover <master name> Force a failover as if the master was not reachable, and without asking for agreement to other Sentinels (however a new version of the configuration will be published so that the other Sentinels will update their configurations).
    SENTINEL ckquorum <master name> Check if the current Sentinel configuration is able to reach the quorum needed to failover a master, and the majority needed to authorize the failover. This command should be used in monitoring systems to check if a Sentinel deployment is ok.
    SENTINEL flushconfig Force Sentinel to rewrite its configuration on disk, including the current Sentinel state. Normally Sentinel rewrites the configuration every time something changes in its state (in the context of the subset of the state which is persisted on disk across restart). However sometimes it is possible that the configuration file is lost because of operation errors, disk failures, package upgrade scripts or configuration managers. In those cases a way to to force Sentinel to rewrite the configuration file is handy. This command works even if the previous configuration file is completely missing.
    Reconfiguring Sentinel at Runtime
    Starting with Redis version 2.8.4, Sentinel provides an API in order to add, remove, or change the configuration of a given master. Note that if you have multiple sentinels you should apply the changes to all to your instances for Redis Sentinel to work properly. This means that changing the configuration of a single Sentinel does not automatically propagates the changes to the other Sentinels in the network.

    The following is a list of SENTINEL sub commands used in order to update the configuration of a Sentinel instance.

    SENTINEL MONITOR <name> <ip> <port> <quorum> This command tells the Sentinel to start monitoring a new master with the specified name, ip, port, and quorum. It is identical to the sentinel monitor configuration directive in sentinel.conf configuration file, with the difference that you can't use an hostname in as ip, but you need to provide an IPv4 or IPv6 address.
    SENTINEL REMOVE <name> is used in order to remove the specified master: the master will no longer be monitored, and will totally be removed from the internal state of the Sentinel, so it will no longer listed by SENTINEL masters and so forth.
    SENTINEL SET <name> <option> <value> The SET command is very similar to the CONFIG SET command of Redis, and is used in order to change configuration parameters of a specific master. Multiple option / value pairs can be specified (or none at all). All the configuration parameters that can be configured via sentinel.conf are also configurable using the SET command.

    更多参考 redis官网链接
*/

class RedisSentinel
{
    protected $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
    }

    /**
     * 连接redis
     * @param  [type]  $host [description]
     * @param  integer $port [description]
     * @return [type]        [description]
     */
    public function connect($host, $port = 26379)
    {
        if (!$this->redis->connect($host, $port)) {
            return false;
        }

        return true;
    }

    /**
     * ping
     * @return [type] [description]
     */
    public function ping()
    {
        return $this->redis->ping();
    }

    /**
     * 所有主节点信息
     * @return [type] [description]
     */
    public function masters()
    {
        return $this->_parseArray2Map($this->redis->rawCommand('SENTINEL', 'masters'));
    }

    /**
     * 根据主节点名 获取节点信息
     * @param  string $masterName [description]
     * @return [type]             [description]
     */
    public function master($masterName = '')
    {
        return $this->_parseArray2Map($this->redis->rawCommand('SENTINEL', 'master', $masterName));
    }

    /**
     * 获取从节点信息
     * @param  [type] $masterName [description]
     * @return [type]             [description]
     */
    public function slaves($masterName)
    {
        return $this->_parseArray2Map($this->redis->rawCommand('SENTINEL', 'slaves', $masterName));
    }

    /**
     * 获取哨兵信息
     * @param  [type] $masterName [description]
     * @return [type]             [description]
     */
    public function sentinels($masterName)
    {
        return $this->_parseArray2Map($this->redis->rawCommand('SENTINEL', 'sentinels', $masterName));
    }

    /**
     * 根据主节点名称 获取主节点的ip 端口信息
     * @param  [type] $masterName [description]
     * @return [type]             [description]
     */
    public function getMasterAddrByName($masterName)
    {
        $data = $this->redis->rawCommand('SENTINEL', 'get-master-addr-by-name', $masterName);

        return [
            'ip' => $data[0],
            'port' => $data[1]
        ];
    }

    public function reset($pattern)
    {
        return $this->redis->rawCommand('SENTINEL', 'reset', $pattern);
    }

    public function failOver($masterName)
    {
        return $this->redis->rawCommand('SENTINEL', 'failover', $masterName) === 'OK';
    }

    public function ckquorum($masterName)
    {
        return $this->redis->rawCommand('SENTINEL', 'ckquorum', $masterName);
    }

    public function flushConfig()
    {
        return $this->redis->rawCommand('SENTINEL', 'flushconfig');
    }

    public function monitor($masterName, $ip, $port, $quorum)
    {
        return $this->redis->rawCommand('SENTINEL', 'monitor', $masterName, $ip, $port, $quorum);
    }

    public function remove($masterName)
    {
        return $this->redis->rawCommand('SENTINEL', 'remove', $masterName);
    }

    public function set($masterName, $option, $value)
    {
        return $this->redis->rawCommand('SENTINEL', 'set', $masterName, $option, $value);
    }

    public function info()
    {
        return $this->redis->info();
    }

    public function __destruct()
    {
        try {
            $this->redis->close();
        } catch (\Exception $e) {
        }
    }

    protected function _parseArray2Map($data = [])
    {
        $result = [];
        $count = count($data);
        for ($i = 0; $i < $count;) {
            $record = $data[$i];
            if (is_array($record)) {
                $result[] = $this->_parseArray2Map($record);
                $i++;
            } else {
                $result[$record] = $data[$i + 1];
                $i += 2;
            }
        }

        return $result;
    }

}
