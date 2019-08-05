<?php

namespace Happysir\Lock\Testing;

use Happysir\Lock\Annotation\Mapping\DistributedLock;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class DistributedLockBean
 *
 * @Bean()
 */
class DistributedLockBean
{
    /**
     * @DistributedLock(type=DistributedLock::RETRY_TO_GET,ttl=1,retries=10)
     *
     * @return string
     */
    public function retryToGet(): string
    {
        return 'retryToGet';
    }
    
    /**
     * @DistributedLock(type=DistributedLock::NON_BLOCKING)
     *
     * @return string
     */
    public function nonBlocking(): string
    {
        return 'nonBlocking';
    }
    
    /**
     * @DistributedLock(key="req['a']",type=DistributedLock::NON_BLOCKING)
     *
     * @param array $req
     * @return string
     */
    public function expressionLanguage(array $req): string
    {
        return 'expression-language';
    }
    
    /**
     * @DistributedLock(type=DistributedLock::NON_BLOCKING,key="dlock")
     *
     * @return string
     */
    public function nonBlocking2(): string
    {
        return 'nonBlocking2';
    }
    
    /**
     * @DistributedLock(type=DistributedLock::NON_BLOCKING,key="dlock2")
     *
     * @return string
     */
    public function nonBlocking3(): string
    {
        return 'nonBlocking3';
    }
    
    /**
     * @DistributedLock(type=DistributedLock::NON_BLOCKING,errcode=1234)
     *
     * @return string
     */
    public function exceptionCode(): string
    {
        return 'exceptionCode';
    }
    
    /**
     * @DistributedLock(type=DistributedLock::NON_BLOCKING,errmsg="hello world")
     *
     * @return string
     */
    public function exceptionMsg(): string
    {
        return 'exceptionMsg';
    }
}
