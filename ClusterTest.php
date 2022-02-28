<?php

// 参考文档
// https://github.com/phpredis/phpredis/blob/develop/cluster.markdown#readme

$clusterAddressArray = [
    '192.168.88.129:6379',
    '192.168.88.129:6380',
    '192.168.88.130:6381',
    '192.168.88.130:6382',
    '192.168.88.131:6383',
    '192.168.88.131:6384',
];

$cluster = new RedisCluster(null, $clusterAddressArray);

while (true) {
    $cacheId = "cluster:test:" . mt_rand(1000, 9999);

    $value = mt_rand(10000, 99999);
    $cluster->set($cacheId, $value, 3);

    sleep(1);
    echo "key: ", $cacheId, ", value: ", $cluster->get($cacheId), "\n";
    sleep(1);
}

