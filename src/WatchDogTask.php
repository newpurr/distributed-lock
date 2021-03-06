<?php

namespace Happysir\Lock;

use Happysir\Lock\Concern\System;
use Happysir\Lock\Contract\LockInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Co;
use Swoft\Log\Helper\CLog;
use Swoole\Coroutine;
use Throwable;
use function context;

/**
 * Class WatchDog
 *
 * @package Happysir\Lock
 * @Bean(name="watchDog",scope=Bean::PROTOTYPE)
 */
class WatchDogTask implements Contract\WatchDogInterface
{
    use System;
    
    /**
     * watchdog sentinel automatic renewal mechanism
     *
     * @param \Happysir\Lock\Contract\LockInterface $lock
     * @return bool
     * @throws \Throwable
     */
    public function sentinel(LockInterface $lock) : bool
    {
        $workerId = $this->getWorkId();
        $tid      = Co::tid();
        
        CLog::debug('worker[%s] co[%s] successfully initialize the watchdog task', $workerId, $tid);
        
        $ttl = $lock->lockTtl();
        $sleepTime = $ttl > 1 ? $ttl - 1 : 0.5;
        Coroutine::sleep($sleepTime);
        
        while ($lock->isAlive()) {
            
            try {
                context();
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
