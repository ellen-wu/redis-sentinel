#!/bin/bash
# redis addslots

start=$1
end=$2
ip=$3
port=$4

for slot in `seq ${start} ${end}`
do
    echo "slot: ${slot}"
    /usr/local/redis/bin/redis-cli -h ${ip} -p ${port} cluster addslots ${slot}
done
