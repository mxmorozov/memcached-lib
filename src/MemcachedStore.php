<?php
/**
 * Created by PhpStorm.
 * User: Maksim Morozov <maxpower656@gmail.com>
 * Date: 14.05.2022
 * Time: 18:01
 */

namespace lib;

use Graze\TelnetClient\TelnetClient;
use Exception;

class MemcachedStore
{
    private TelnetClient $telnetClient;

    public function __construct(string $dsn = '127.0.0.1:11211')
    {
        $this->telnetClient = TelnetClient::factory();
        $this->telnetClient->connect($dsn, prompt: '', lineEnding: "\r\n");
    }

    public function get($key)
    {
        $this->telnetClient->setPrompt('END');
        $resp = $this->telnetClient->execute("get {$key}");
        $text = $resp->getResponseText();

        if ($text == '') {
            return null;
        } else {
            [, $value] = explode("\r\n", $text, 2);

            return $value ?? null;
        }
    }

    public function set(string $key, string $value, int $exptime = 3600): bool
    {
        if (str_contains($key, "\r\n")) {
            throw new Exception('\r\n in keys not allowed');
        }
        if (str_contains($value, "END\r\n")) {
            throw new Exception('Prohibited char sequence in value: END\r\n');
        }
        $this->telnetClient->setPrompt('');
        $bytes = mb_strlen($value, '8bit');
        $resp = $this->telnetClient->execute("set {$key} 0 {$exptime} {$bytes}\r\n{$value}");
        return match ($resp->getResponseText()) {
            'STORED' => true,
            default => false
        };
    }

    public function delete($key): bool
    {
        $this->telnetClient->setPrompt('');
        $resp = $this->telnetClient->execute("delete {$key}");
        return match ($resp->getResponseText()) {
            'DELETED' => true,
            default => false
        };
    }
}
