<?php

namespace Happysir\Lock\Unit;

use Happysir\Lock\Testing\DistributedLockBean;
use PHPUnit\Framework\TestCase;
use Swoft\Bean\BeanFactory;

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
     * @expectedException \Happysir\Lock\Exception\DistributedLockException
     */
    public function testNonBlocking()
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('nonBlocking', $bean->nonBlocking());
        $this->assertEquals('nonBlocking', $bean->nonBlocking());
    }
    
    /**
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function testNonBlocking2()
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('nonBlocking3', $bean->nonBlocking3());
        $this->assertEquals('nonBlocking2', $bean->nonBlocking2());
    }
    
    /**
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @expectedException \Happysir\Lock\Exception\DistributedLockException
     * @expectedExceptionCode 1234
     */
    public function testExceptionCode()
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('exceptionCode', $bean->exceptionCode());
        $this->assertEquals('exceptionCode', $bean->exceptionCode());
    }
    
    /**
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @expectedException \Happysir\Lock\Exception\DistributedLockException
     * @expectedExceptionMessage hello world
     */
    public function testExceptionMsg()
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('exceptionMsg', $bean->exceptionMsg());
        $this->assertEquals('exceptionMsg', $bean->exceptionMsg());
    }
    
    /**
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function testRetryToGet()
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('retryToGet', $bean->retryToGet());
        $this->assertEquals('retryToGet', $bean->retryToGet());
    }
}
