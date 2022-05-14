<?php

/**
 * Created by PhpStorm.
 * User: Maksim Morozov <maxpower656@gmail.com>
 * Date: 14.05.2022
 * Time: 17:42
 */

namespace lib;

class MemcachedConnector
{

    public function connect(string $dsn): \Graze\TelnetClient\TelnetClient
    {
        $client = \Graze\TelnetClient\TelnetClient::factory();
        $client->connect($dsn, prompt: '', lineEnding: "\r\n");
        $client->set
        return $client;
    }
}

