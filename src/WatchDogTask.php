<?php

namespace Happysir\Lock;

use Happysir\Lock\Contract\LockInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Co;
use Swoft\Context\Context;
use Swoft\Log\Helper\CLog;
use Swoole\Coroutine;
use Throwable;

/**
 * Class WatchDog
 *
 * @package Happysir\Lock
 * @Bean(name="watchDog",scope=Bean::PROTOTYPE)
 */
class WatchDogTask implements Contract\WatchDogInterface
{
    
    /**
     * watchdog sentinel automatic renewal mechanism
     *
     * @param \Happysir\Lock\Contract\LockInterface $lock
     * @return bool
     * @throws \Throwable
     */
    public function sentinel(LockInterface $lock) : bool
    {
        $workerId = server()->getSwooleServer()->worker_id;
        $tid      = Co::tid();
        
        CLog::debug('worker[%s] co[%s] successfully initialize the watchdog task', $workerId, $tid);
        
        $ttl = $lock->lockTtl();
        $sleepTime = $ttl > 1 ? $ttl - 1 : 0.5;
        Coroutine::sleep($sleepTime);
        
        while ($lock->isAlive()) {
            
            try {
                Context::mustGet();
            } catch (Throwable $e) {
                CLog::debug('worker[%s] co[%s] cleanup watch dog task after request completed', $workerId, $tid);
                
                return true;
            }
            
            if (!$lock->keepAlive($ttl)) {
                CLog::debug('worker[%s] co[%s] cleanup watch dog task when renewal failure', $workerId, $tid);
                
                return true;
            }
            
            CLog::debug('worker[%s] co[%s] watch dog successful renewal %s s', $workerId, $tid, $ttl);
    
            Coroutine::sleep($sleepTime);
        }
        
        CLog::debug('worker[%s] co[%s] cleanup watch dog task when the lock has expired', $workerId, $tid);
        
        return true;
    }
}
