<?php

namespace Happysir\Lock;

use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Uuid;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Log\Helper\CLog;

/**
 * Class UuidManage
 *
 * @package Happysir\Lock
 * @Bean("uuidManage")
 */
class UuidManage implements Contract\UuidIterface
{
    /**
     * generate a uuid
     *
     * @param string $flag
     * @return string
     * @throw UnsatisfiedDependencyException
     */
    public function generate(string $flag = '') : string
    {
        try {
            $uuid5 = Uuid::uuid5(Uuid::NAMESPACE_DNS, $flag ? : time());
            
            return $uuid5->toString();
        } catch (UnsatisfiedDependencyException $e) {
            CLog::error($e->getMessage() . ',code:' . $e->getCode());
            throw $e;
        }
    }
}
