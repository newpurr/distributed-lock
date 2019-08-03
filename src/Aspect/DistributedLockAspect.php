<?php

namespace Happysir\Lock\Aspect;

use Happysir\Lock\Annotation\Mapping\DistributedLock;
use Happysir\Lock\Contract\LockInterface;
use Happysir\Lock\RedisLock;
use Swoft\Aop\Annotation\Mapping\After;
use Swoft\Aop\Annotation\Mapping\AfterReturning;
use Swoft\Aop\Annotation\Mapping\AfterThrowing;
use Swoft\Aop\Annotation\Mapping\Around;
use Swoft\Aop\Annotation\Mapping\Aspect;
use Swoft\Aop\Annotation\Mapping\Before;
use Swoft\Aop\Annotation\Mapping\PointAnnotation;
use Swoft\Aop\Point\JoinPoint;
use Swoft\Aop\Point\ProceedingJoinPoint;
use Swoft\Co;

/**
 * Class DistributedLockAspect
 * @Aspect(order=1)
 * @PointAnnotation(include={DistributedLock::class})
 */
class DistributedLockAspect
{
    /**
     * @var \Happysir\Lock\RedisLock[]
     */
    protected $lock;
    
    /**
     * @Around()
     *
     * @param ProceedingJoinPoint $proceedingJoinPoint
     *
     * @return mixed
     * @throws \Happysir\Lock\Exception\RedisLockException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Throwable
     */
    public function around(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // Before around
        $this->tryLock();
        
        $result = $proceedingJoinPoint->proceed();
        
        // After around
        
        return $result;
    }
    
    /**
     * @Before()
     */
    public function before()
    {
    
    }
    
    /**
     * @After()
     * @throws \Throwable
     */
    public function after()
    {
        // after
        $this->unLock();
    }
    
    /**
     * @AfterReturning()
     *
     * @param JoinPoint $joinPoint
     *
     * @return mixed
     */
    public function afterReturn(JoinPoint $joinPoint)
    {
        $ret = $joinPoint->getReturn();

        // After return
        
        return $ret;
    }
    
    /**
     * @param \Throwable $throwable
     *
     * @throws \Throwable
     * @AfterThrowing()
     */
    public function afterThrowing(\Throwable $throwable)
    {
        throw $throwable;
    }
    
    /**
     * try to acquire a lock
     *
     * @return bool
     * @throws \Happysir\Lock\Exception\RedisLockException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Throwable
     */
    protected function tryLock() : bool
    {
        // TODO 替换成注解收集的信息
        if (!$this->getRedisLock()->tryLock('test', 50)) {
            throw new RedisLockException('worker[%s] co[%s] failed to acquire lock', server()->getSwooleServer()->worker_id, Co::tid());
        }
        
        return true;
    }
    
    /**
     * if the lock has not expired, perform an unlock operation
     *
     * @return bool
     * @throws \Throwable
     */
    protected function unLock() : bool
    {
        if ($this->getRedisLock()->isAlive()) {
            $this->getRedisLock()->unLock();
        }
    
        $this->releaseRedisLock();
        
        return true;
    }
    
    /**
     * 获取锁
     *
     * @return \Happysir\Lock\Contract\LockInterface
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    protected function getRedisLock() : LockInterface
    {
        if (!isset($this->lock[Co::tid()])) {
            $this->lock[Co::tid()] = bean(RedisLock::class);
        }
        
        return $this->lock[Co::tid()];
    }
    
    /**
     * 释放锁对象
     *
     * @return bool
     */
    protected function releaseRedisLock() : bool
    {
        unset($this->lock[Co::tid()]);
    
        return true;
    }
}
