<?php


// $c = new ReflectionExtension('Redis');

$c = new ReflectionClass(new RedisSentinel());

print_r($c->getMethods());
