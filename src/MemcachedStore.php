<?php
/**
 * Created by PhpStorm.
 * User: Maksim Morozov <maxpower656@gmail.com>
 * Date: 14.05.2022
 * Time: 18:01
 */

namespace lib;

class MemcachedStore
{
    private \Graze\TelnetClient\TelnetClient $telnetClient;
    private string $prefix;

    public function __construct($telnetClient)
    {
        $this->telnetClient = $telnetClient;
    }

    public function get($key)
    {
        $resp = $this->telnetClient->execute("get {$key}");
        if ($resp->getResponseText() == 'END') {
            return null;
        } else {
//            $this->telnetClient->;
            return $resp;
        }

    }

    public function set(string $key, string $value, int $exptime = 3600)
    {
        $bytes = mb_strlen($value, '8bit');
        $resp = $this->telnetClient->execute("set {$key} 0 {$exptime} {$bytes}\r\n{$value}");
        return $resp->getResponseText();

    }
}
