<?php declare(strict_types = 1);

namespace Happysir\Lock;

use Happysir\Lock\Contract\LockInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Primary;
use Swoft\Co;
use Swoft\Context\Context;
use Swoft\Log\Helper\Log;
use Swoft\Redis\Connection\Connection;
use Swoft\Redis\Redis;
use Swoole\Coroutine;
use Throwable;

/**
 * Class RedisLock
 * @Primary()
 * @Bean(scope=Bean::PROTOTYPE)
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
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
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
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Throwable
     */
    public function lock(string $key, int $ttl = 3, int $retries = 3) : bool
    {
        $times = 0;
        
        while ($times < $retries) {
            
            if ($this->doLock($key, $ttl)) {
                return true;
            }
            
            Coroutine::sleep($ttl);
            
            $times++;
        }
        
        return false;
    }
    
    /**
     * Let the lock last for N seconds, the default N is 3
     *
     * @param int $ttl
     *
     * @return bool
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
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
            Log::getLogger()->info($e->getMessage());
            throw $e;
        }
    }
    
    /**
     * release lock
     *
     * @return bool
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Throwable
     */
    public function unLock() : bool
    {
        try {
            return (bool)$this->getConnection()->del($this->key) >= 0;
        } catch (Throwable $e) {
            Log::getLogger()->info($e->getMessage());
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
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Throwable
     */
    protected function doLock(string $key, int $ttl = 3) : bool
    {
        $this->key = $key;
        
        $parameters = [$this->key, 1, ['nx', 'ex' => $ttl]];
        
        try {
            $result = (bool)$this->getConnection()->command('set', $parameters);
            
            if ($result) {
                Co::create(function () use ($ttl) {
                    $this->watchDog($ttl);
                }, false);
            }
            
            return $result;
        } catch (Throwable $e) {
            Log::getLogger()->info($e->getMessage());
            throw $e;
        }
    }
    
    /**
     * renewal of watchdog lease term
     *
     * @param int $ttl
     *
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Throwable
     */
    protected function watchDog(int $ttl = 3)
    {
        while (true) {
            $sleepTime = $ttl - 3;
            
            Coroutine::sleep($sleepTime);
            
            if (!Context::get() || !$this->keepAlive($ttl)) {
                Log::getLogger()->info('Cleanup watch dog task');
                break;
            }
        }
    }
}
