<?php declare(strict_types = 1);

namespace Happysir\Lock;

use Happysir\Lock\Annotation\Mapping\DistributedLock;

/**
 * Class DistributedLockRegister
 *
 * @package Happysir\Lock
 */
class DistributedLockRegister
{
    /**
     * @var array
     *
     * @example
     * [
     *     'className' => [
     *         'method' => new DistributedLock()
     *     ]
     * ]
     */
    private static $locks = [];
    
    /**
     * Register aspect
     *
     * @param string          $className
     * @param string          $method
     * @param DistributedLock $lock
     */
    public static function registerLock(
        string $className,
        string $method,
        DistributedLock $lock
    ) : void {
        self::$locks[$className][$method] = $lock;
    }
    
    /**
     * @param string $className
     * @param string $method
     *
     * @return DistributedLock
     */
    public static function getLock(string $className, string $method) : DistributedLock
    {
        return self::$locks[$className][$method];
    }
    
    /**
     * getLocks
     *
     * @return array
     */
    public static function getLocks() : array
    {
        return self::$locks;
    }
}
