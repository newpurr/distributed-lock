<?php

namespace Happysir\Lock\Exception;

/**
 * Class DistributedLockException
 *
 * @package Happysir\Lock\Exception
 */
class DistributedLockException extends \Exception
{
    protected $code = 39281890;
    
    protected $message = 'failed to acquire lock';
}
