<?php

use Graze\TelnetClient\TelnetClient;
use lib\MemcachedConnector;

test('connect', function () {
    $memcached = (new MemcachedConnector())->connect('127.0.0.1:11211');
    expect($memcached)->toBeInstanceOf(TelnetClient::class);
});

test('create store', function () {
    $telnet = (new MemcachedConnector())->connect('127.0.0.1:11211');
    $store = new \lib\MemcachedStore($telnet);
    expect($store)->not()->toBeNull();
});

test('get and set', function () {
    $telnet = (new MemcachedConnector())->connect('127.0.0.1:11211');
    $store = new \lib\MemcachedStore($telnet);
    $key = 'key' . time();

    expect($store->get($key))->toBeNull();
    expect($store->set($key, 'asd'))->toEqual('STORED');
    var_dump($store->get($key));exit;

    expect($store->get($key))->toEqual('END');
});
