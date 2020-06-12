<?php

namespace Gsom1;

use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class CacheGateway
{
    /** @var CacheItemPoolInterface  */
    private $cache;

    private $destination;

    /** @var int */
    private $ttl;

    public function __construct(CacheItemPoolInterface $cache, $destination, int $ttl = 3600)
    {
        $this->cache = $cache;
        $this->destination = $destination;
        $this->ttl = $ttl;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->destination, $name)) {
            $key = $this->getKey(get_class($this->destination), $name, json_encode($arguments));

            $item = $this->cache->getItem($key);
            if ($item->isHit()) {
                return $item->get();
            }

            $data = call_user_func_array([$this->destination, $name], $arguments);
            $item->set($data);
            $item->expiresAfter($this->ttl);
            $this->cache->save($item);

            return $data;
        }

        throw new Exception('Method does not exist '.$name);
    }

    private function getKey(string ...$str): string
    {
        return md5(implode(func_get_args()));
    }
}
