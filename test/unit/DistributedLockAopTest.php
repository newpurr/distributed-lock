<?php

namespace Happysir\Lock\Unit;

use Happysir\Lock\Testing\DistributedLockBean;
use PHPUnit\Framework\TestCase;
use Swoft\Bean\BeanFactory;

/**
 * Class DistributedLockAopTest
 *
 * @package Happysir\Lock\Unit
 */
class DistributedLockAopTest extends TestCase
{
    /**
     * @expectedException \Happysir\Lock\Exception\DistributedLockException
     */
    public function testNonBlocking() : void
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('nonBlocking', $bean->nonBlocking());
        $this->assertEquals('nonBlocking', $bean->nonBlocking());
    }
    
    /**
     * testNonBlocking2
     */
    public function testNonBlocking2() : void
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('nonBlocking3', $bean->nonBlocking3());
        $this->assertEquals('nonBlocking2', $bean->nonBlocking2());
    }
    
    /**
     * @expectedException \Happysir\Lock\Exception\DistributedLockException
     * @expectedExceptionCode 1234
     */
    public function testExceptionCode() : void
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('exceptionCode', $bean->exceptionCode());
        $this->assertEquals('exceptionCode', $bean->exceptionCode());
    }
    
    /**
     * @expectedException \Happysir\Lock\Exception\DistributedLockException
     * @expectedExceptionMessage hello world
     */
    public function testExceptionMsg() : void
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('exceptionMsg', $bean->exceptionMsg());
        $this->assertEquals('exceptionMsg', $bean->exceptionMsg());
    }
    
    /**
     * testRetryToGet
     */
    public function testRetryToGet() : void
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('retryToGet', $bean->retryToGet());
        $this->assertEquals('retryToGet', $bean->retryToGet());
    }
    
    /**
     * testExpressionLanguage
     */
    public function testExpressionLanguage() : void
    {
        /* @var DistributedLockBean $bean */
        $bean = BeanFactory::getBean(DistributedLockBean::class);
        
        $this->assertEquals('expression-language', $bean->expressionLanguage(['a' => 1]));
    }
}
