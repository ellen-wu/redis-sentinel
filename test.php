<?php

include 'RedisSentinel.php';
require 'RedisSentinelPool.php';

// $sentinel = new RedisSentinel();

// $sentinel->connect('192.168.88.129', 26379);


// // print_r($sentinel->masters());
// print_r($sentinel->master("redis-master-test"));


// php cli运行 然后模拟主redis下线
$masterName = 'redis-master-test';

$pool = new RedisSentinelPool();

$pool->addSentinel('192.168.88.129', 26379);
$pool->addSentinel('192.168.88.130', 26380);
$pool->addSentinel('192.168.88.131', 26381);

// print_r($pool->slaves($masterName));die();
// print_r($pool->sentinels($masterName));die();

while (true) {
    try {
        $redis = $pool->getRedis($masterName);

        $cacheId = 'master:test:' . mt_rand(0, 99999);
        $expireTime = 1;

        $redis->set($cacheId, mt_rand(1000, 9999), $expireTime);

        usleep(800000);
        echo $redis->get($cacheId), "\n";

        sleep(2);
    } catch (\Exception $e) {
        echo "下线咯: " . $e->getMessage(), "\n";
    }
}

// $info = $redis->info();
// print_r($info);

// print_r($pool->master($masterName));



