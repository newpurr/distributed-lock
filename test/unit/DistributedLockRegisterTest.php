<?php

namespace Happysir\Lock\Unit;

use Happysir\Lock\Annotation\Mapping\DistributedLock;
use Happysir\Lock\DistributedLockRegister;
use PHPUnit\Framework\TestCase;

class DistributedLockRegisterTest extends TestCase
{
    /**
     * @return array
     */
    public function testRregisterLock() : array
    {
        $lock = new DistributedLock([]);
        DistributedLockRegister::registerLock(__CLASS__, __METHOD__, $lock);
        $this->assertNotEmpty(DistributedLockRegister::getLocks());
        $this->assertSame($lock, DistributedLockRegister::getLock(__CLASS__, __METHOD__));
    
        return [];
    }
}
