<?php declare(strict_types = 1);

namespace Happysir\Lock\Contract;

/**
 * Interface LockInterface
 *
 * @version 1.0
 * @package Happysir\Contract
 */
interface LockInterface
{
    /**
     * get lock，This method will return fasle directly after the lock is failed.
     *
     * @param string $key lock unique identifier
     *
     * @param int    $ttl
     *
     * @return bool
     */
    public function tryLock(string $key, int $ttl = 3) : bool;
    
    /**
     * get lock，This method will return fasle directly after the lock is failed.
     *
     * @param string $key     lock unique identifier
     *
     * @param int    $ttl
     * @param int    $retries number of retries
     *
     * @return bool
     */
    public function lock(string $key, int $ttl = 3, int $retries = 3) : bool;
    
    /**
     * Let the lock last for N seconds, the default N is 3
     *
     * @param int $ttl
     *
     * @return bool
     */
    public function keepAlive(int $ttl = 3) : bool;
    
    /**
     * check if the lock is valid
     *
     * @return bool
     */
    public function isAlive() : bool;
    
    /**
     * release lock
     *
     * @return bool
     */
    public function unLock() : bool;
}
