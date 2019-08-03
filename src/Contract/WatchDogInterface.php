<?php

namespace Happysir\Lock\Contract;

/**
 * interface WatchDogInterface
 * WatchDog TaskInterface
 *
 * @package Happysir\Lock\Contract
 */
interface WatchDogInterface
{
    /**
     * watchdog sentinel automatic renewal mechanism
     * Return true if the task completed successfully
     *
     * @param \Happysir\Lock\Contract\LockInterface $lock
     * @return bool
     * @throws \Throwable
     */
    public function sentinel(LockInterface $lock) : bool;
}
