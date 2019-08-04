<?php

namespace Happysir\Lock\Exception;

/**
 * Class RedisLockException
 *
 * @package Happysir\Lock\Exception
 */
class RedisLockException extends \Exception
{
    protected $code = 39281890;
    
    protected $message = 'failed to acquire lock';
}
