<?php

use Graze\TelnetClient\TelnetClient;
use lib\MemcachedStore;

test('connect', function () {
    $store = new MemcachedStore();
    expect($store)->toBeInstanceOf(MemcachedStore::class);
});


test('get and set', function () {
    $store = new MemcachedStore();

    $key = 'key' . time();

    expect($store->get($key))->toBeNull();
    expect($store->set($key, 'asd'))->toBeTrue();
    expect($store->get($key))->toEqual('asd');
    expect($store->delete($key))->toBeTrue();
    expect($store->delete($key . 1))->not()->toBeTrue();
    expect($store->get($key))->toBeNull();
});

test('new lines', function () {
    $store = new MemcachedStore();

    $key = "keysdf" . time();
    $value = "asdfs\r\nsdfsdf";

    expect($store->set($key, $value))->toBeTrue();
    expect($store->get($key))->toEqual($value);
});
