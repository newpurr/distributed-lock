<?php

namespace Happysir\Lock\Unit;

use Happysir\Lock\RedisLock;
use PHPUnit\Framework\TestCase;

/**
 * Class DistributedLockTest
 *
 * @package Happysir\Lock\Unit
 */
class DistributedLockTest extends TestCase
{
    /**
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Throwable
     */
    public function testNonBlocking()
    {
        /* @var RedisLock $bean */
        $bean = bean(RedisLock::class);
        
        $this->assertTrue( $bean->tryLock('test1', 1 ));
        $this->assertTrue( $bean->unLock());
    }
    
    /**
     * @throws \Throwable
     */
    public function testNonBlocking2()
    {
        $bean = new RedisLock();
    
        $this->assertTrue( $bean->tryLock('test11', 1 ));
        $this->assertFalse( $bean->tryLock('test11', 1 ));
        $this->assertTrue( $bean->unLock());
    }
    
    /**
     * @throws \Throwable
     */
    public function testRetryToGet()
    {
        $bean = new RedisLock();
        
        $this->assertTrue( $bean->lock('test12', 1 , 2));
        $this->assertTrue( $bean->lock('test12', 1 , 4));
        $this->assertTrue( $bean->unLock());
    }
}
