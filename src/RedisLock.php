<?php declare(strict_types = 1);

namespace Happysir\Lock;

use Happysir\Lock\Contract\LockInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Primary;
use Swoft\Co;
use Swoft\Context\Context;
use Swoft\Log\Helper\CLog;
use Swoft\Redis\Connection\Connection;
use Swoft\Redis\Redis;
use Swoole\Coroutine;
use Throwable;

/**
 * Class RedisLock
 * @Bean(scope=Bean::PROTOTYPE)
 * @Primary()
 */
class RedisLock implements LockInterface
{
    /**
     * @var string
     */
    private $key;
    
    /**
     * get lock，This method will return fasle directly after the lock is failed.
     *
     * @param string $key lock unique identifier
     * @param int    $ttl
     *
     * @return bool
     * @throws \Throwable
     */
    public function tryLock(string $key, int $ttl = 3) : bool
    {
        return $this->doLock($key, $ttl);
    }
    
    /**
     * get lock，This method will return fasle directly after the lock is failed.
     *
     * @param string $key     lock unique identifier
     * @param int    $ttl
     * @param int    $retries number of retries
     *
     * @return bool
     * @throws \Throwable
     */
    public function lock(string $key, int $ttl = 3, int $retries = 3) : bool
    {
        $times = 0;
        
        while ($times < $retries) {
            
            if ($this->doLock($key, $ttl)) {
                return true;
            }
            
            Coroutine::sleep(0.5);
            
            $times++;
            
            CLog::debug('Try to acquire the lock again, the number of attempts: %d', $times);
        }
        
        return false;
    }
    
    /**
     * Let the lock last for N seconds, the default N is 3
     *
     * @param int $ttl
     *
     * @return bool
     * @throws \Throwable
     */
    public function keepAlive(int $ttl = 3) : bool
    {
        $lua = <<<LUA
                -- get the remaining life time of the key
                local leftoverTtl = redis.call("TTL", KEYS[1]);
                
                -- never expired key
                if (leftoverTtl == -1) then
                    return -1;
                end;
                
                -- key with remaining time
                if (redis.call("TTL", KEYS[1]) ~= -2) then
                    return redis.call("EXPIRE", KEYS[1], ARGV[1]);
                end;
                
                -- key that does not exist
                return -2;
LUA;
        
        try {
            $eval = $this->getConnection()->eval($lua, [$this->key, $ttl], 1);
            
            return $eval !== -2;
        } catch (Throwable $e) {
            CLog::error($e->getMessage());
            throw $e;
        }
    }
    
    /**
     * check if the lock is valid
     *
     * @return bool
     * @throws \Throwable
     */
    public function isAlive() : bool
    {
        try {
            $eval = $this->getConnection()->ttl($this->key);
            
            return $eval !== -2;
        } catch (Throwable $e) {
            CLog::error($e->getMessage());
            throw $e;
        }
    }
    
    /**
     * release lock
     *
     * @return bool
     * @throws \Throwable
     */
    public function unLock() : bool
    {
        try {
            CLog::debug('release lock');
    
            return (bool)$this->getConnection()->del($this->key) >= 0;
        } catch (Throwable $e) {
            CLog::error($e->getMessage());
            throw $e;
        }
    }
    
    /**
     * get redis connection
     *
     * @return \Swoft\Redis\Connection\Connection
     * @throws \Swoft\Redis\Exception\RedisException
     */
    protected function getConnection() : Connection
    {
        return Redis::connection();
    }
    
    /**
     * get lock
     *
     * @param string $key
     * @param int    $ttl
     *
     * @return bool
     * @throws \Throwable
     */
    protected function doLock(string $key, int $ttl = 3) : bool
    {
        $this->key = $key;
        
        try {
            $parameters = [$this->key, 1, ['nx', 'ex' => $ttl]];
            $result     = (bool)$this->getConnection()->command('set', $parameters);
            
            if ($result) {
                CLog::debug('successfully hold lock, initialize the watchdog task');
                Co::create(function () use ($ttl) {
                    $this->watchDog($ttl);
                }, false);
            }
            
            return $result;
        } catch (Throwable $e) {
            CLog::error($e->getMessage());
            throw $e;
        }
    }
    
    /**
     * renewal of watchdog lease term
     *
     * @param int $ttl
     *
     * @throws \Throwable
     */
    protected function watchDog(int $ttl = 3)
    {
        $sleepTime = $ttl > 1 ? $ttl - 1 : 0.5;
        
        while (true) {
            Coroutine::sleep($sleepTime);
            
            try {
                Context::mustGet();
            } catch (Throwable $e) {
                CLog::debug('cleanup watch dog task after request completed');
                break;
            }
            
            if (!$this->isAlive()) {
                CLog::debug('cleanup watch dog task when the lock has expired');
                break;
            }
            
            if (!$this->keepAlive($ttl)) {
                CLog::debug('cleanup watch dog task when renewal failure');
                break;
            }
            
            CLog::debug('watch dog successful renewal %s s', $ttl);
        }
    }
}
