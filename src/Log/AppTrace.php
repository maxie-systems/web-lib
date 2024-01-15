<?php

namespace MaxieSystems\Log;

class AppTrace
{
    public function __construct()
    {
        $this->time = new \DateTimeImmutable();
        $values = str_split($this->time->getTimestamp() ** 2, 2);
        $values = array_filter($values, fn (string $v) => $v[0] !== '0');
        $this->id = hash(
            'crc32b',
            random_int(PHP_INT_MIN, PHP_INT_MAX)
            . random_bytes($values[array_rand($values, 1)])
            . str_shuffle($this->time->format($this->time::RFC3339_EXTENDED))
        );
        //$this->add('entrypoint', ...$args)
    }

    public function add()
    {
        # add an entity
        //$this->data[] = [$this->id, $type, ];
    }

    public function __destruct()
    {
/*
Logs|Log
Error|Exception
Context: PHP superglobals, request headers, other
Context snap
Source: CMD, GET|POST|PUT|...
Data\Context Filters
Storage
Encoding\decoding or serialization\de--- and Hashing (to make item unique).
*/
/*
crc32id $remote_addr timestamp
crc32id entrypoint  url GET https://developer.mozilla.org/api/v1/whoami
crc32id entrypoint  GET https://developer.mozilla.org/api/v1/whoami
crc32id entrypoint  CMD command line arguments and options
crc32id data|context [$_GET, $_POST, $_SERVER, etc]
crc32id error Message. Something went wrong... $context
crc32id exit    200
crc32id exit    -
*/
        # write data into storage
    }

    public function __debugInfo()
    {
        return ['id' => $this->id, 'time' => $this->time->format($this->time::RFC3339_EXTENDED)];
    }

    public readonly string $id;
    private readonly \DateTimeImmutable $time;
}
