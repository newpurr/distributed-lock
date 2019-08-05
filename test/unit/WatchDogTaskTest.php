<?php

namespace Happysir\Lock\Unit;

use Co\Channel;
use Happysir\Lock\RedisLock;
use Happysir\Lock\WatchDogTask;
use PHPUnit\Framework\TestCase;

class WatchDogTaskTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testSentinel()
    {
        $lock = new RedisLock();
        $lock->tryLock('testSentinel', 1);
        $watchDog = new WatchDogTask();
    
        $channel = new Channel();
        go(
            function () use ($watchDog, $lock, $channel) {
            $this->assertTrue($watchDog->sentinel($lock));
            $channel->push(1);
        });
        $channel->pop();
    }
}
