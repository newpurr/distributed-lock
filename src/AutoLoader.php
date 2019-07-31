<?php declare(strict_types = 1);

namespace Happysir\Lock;

use Happysir\Lock\Contract\LockInterface;
use Swoft\SwoftComponent;

/**
 * Class AutoLoader
 *
 * @since 2.0
 */
class AutoLoader extends SwoftComponent
{
    /**
     * Get namespace and dirs
     *
     * @return array
     */
    public function getPrefixDirs() : array
    {
        return [
            __NAMESPACE__ => __DIR__,
        ];
    }
    
    /**
     * @return array
     */
    public function metadata() : array
    {
        return [];
    }
    
    /**
     * @return array
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function beans() : array
    {
        return [
            // LockInterface::class => [
            //     'class' => RedisLock::class
            // ],
            // 'distributedLock' => [
            //     'class' => bean(LockInterface::class)
            // ]
        ];
    }
}
