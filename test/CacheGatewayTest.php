<?php


use Gsom1\CacheGateway;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheGatewayTest extends TestCase
{
    /** @var CacheGateway */
    private $gateway;

    /** @var MockObject | CacheItemPoolInterface */
    private $pool;

    /** @var MockObject | CacheItemInterface */
    private $item;

    /** @var ArrayObject */
    private $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->item = $this->getMockBuilder(CacheItemInterface::class)
            ->getMock()
        ;

        $this->pool = $this->getMockBuilder(CacheItemPoolInterface::class)
            ->getMock()
        ;

        $this->destination = new ArrayObject();
        $this->gateway = new CacheGateway($this->pool, $this->destination);
    }

    public function testCached()
    {
        $this->item->method('isHit')->willReturn(true);
        $this->item->method('get')->willReturn(123);
        $this->pool->method('getItem')->willReturn($this->item);

        $result = $this->gateway->offsetGet('test');

        $this->assertEquals(123, $result);
    }

    public function testCache()
    {
        $this->destination->offsetSet('test', 123);
        $this->item->method('isHit')->willReturn(false);
        $this->pool->method('getItem')->willReturn($this->item);
        $this->item->expects($this->once())->method('set');
        $this->pool->expects($this->once())->method('save');
        $result = $this->gateway->offsetGet('test');

        $this->assertEquals(123, $result);
    }

    public function testWrongMethod()
    {
        $this->expectException(Exception::class);
        $this->gateway->qwe('test');
    }
}
