<?php

namespace Happysir\Lock\Exception;

/**
 * Class RedisLockException
 *
 * @package Happysir\Lock\Exception
 */
class RedisLockException extends \Exception
{
    protected $message = 'failed to acquire lock';
}
