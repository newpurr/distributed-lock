<?php

namespace Happysir\Lock\Contract;

use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/**
 * interface UuidIterface
 *
 * @package Happysir\Lock\Contract
 */
interface UuidIterface
{
    /**
     * @param string $flag
     * @return string
     * @throw UnsatisfiedDependencyException
     */
    public function generate(string $flag = '') : string;
}
