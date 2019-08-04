<?php

namespace Happysir\Lock\Aspect;

use Happysir\Lock\Annotation\Mapping\DistributedLock;
use Happysir\Lock\Contract\LockInterface;
use Happysir\Lock\DistributedLockRegister;
use Happysir\Lock\Exception\RedisLockException;
use Happysir\Lock\RedisLock;
use ReflectionException;
use Swoft\Aop\Annotation\Mapping\After;
use Swoft\Aop\Annotation\Mapping\AfterReturning;
use Swoft\Aop\Annotation\Mapping\AfterThrowing;
use Swoft\Aop\Annotation\Mapping\Around;
use Swoft\Aop\Annotation\Mapping\Aspect;
use Swoft\Aop\Annotation\Mapping\Before;
use Swoft\Aop\Annotation\Mapping\PointAnnotation;
use Swoft\Aop\Point\JoinPoint;
use Swoft\Aop\Point\ProceedingJoinPoint;
use Swoft\Aop\Proxy;
use Swoft\Co;
use Swoft\Context\Context;
use Swoft\Stdlib\Reflections;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Throwable;

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
        $this->tryLock($proceedingJoinPoint);

        return $proceedingJoinPoint->proceed();
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
        return $joinPoint->getReturn();
    }
    
    /**
     * @param \Throwable $throwable
     *
     * @throws \Throwable
     * @AfterThrowing()
     */
    public function afterThrowing(Throwable $throwable)
    {
        throw $throwable;
    }
    
    /**
     * try to acquire a lock
     *
     * @param \Swoft\Aop\Point\ProceedingJoinPoint $proceedingJoinPoint
     * @return bool
     * @throws \Happysir\Lock\Exception\RedisLockException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Throwable
     */
    protected function tryLock(ProceedingJoinPoint $proceedingJoinPoint) : bool
    {
        $args      = $proceedingJoinPoint->getArgs();
        $target    = $proceedingJoinPoint->getTarget();
        $method    = $proceedingJoinPoint->getMethod();
        
        // get class name
        $className = get_class($target);
        $className = Proxy::getOriginalClassName($className);
    
        // get config
        $config = DistributedLockRegister::getLock($className, $method);
        
        // init key
        $key    = $config->getKey();
        $key = !empty($key)
            ? $this->evaluateKey($key, $className, $method, $args)
            : md5(sprintf('%s:%s', $className, $method));
        
        // get lock
        $result = $config->isNonBlocking()
            ? $this->getRedisLock()->tryLock($key, $config->getTtl())
            : $this->getRedisLock()->lock($key, $config->getTtl(), $config->getRetries());
        
        if (!$result) {
            $errCode = $config->getErrCode();
            $errMsg  = $config->getErrMsg();
            if (!$errMsg) {
                $errMsg = sprintf('worker[%s] co[%s] failed to acquire lock', server()->getSwooleServer()->worker_id, Co::tid());
            }
            
            throw new RedisLockException($errMsg, $errCode);
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
    
    /**
     * @param string $key
     * @param string $className
     * @param string $method
     * @param array  $params
     *
     * @return string
     * @throws ReflectionException
     */
    private function evaluateKey(string $key, string $className, string $method, array $params): string
    {
        $values   = [];
        $rcMethod = Reflections::get($className);
        $rcParams = $rcMethod['methods'][$method]['params'] ?? [];
        
        $index = 0;
        foreach ($rcParams as $rcParam) {
            [$pName] = $rcParam;
            $values[$pName] = $params[$index];
            $index++;
        }
        
        // Inner vars
        $values['CLASS']   = $className;
        $values['METHOD']  = $method;
        $values[$key]      = $key;
    
        $context = Context::get();
        if ($context !== null && !isset($values['request'])) {
            $values['request'] = $context->getRequest();
        }
        
        // Parse express language
        $el = new ExpressionLanguage();
        
        return $el->evaluate($key, $values);
    }
}
