<?php

namespace Happysir\Lock\Unit;

use Happysir\Lock\Annotation\Mapping\DistributedLock;
use Happysir\Lock\Annotation\Parser\DistributedLockParser;
use Happysir\Lock\DistributedLockRegister;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Swoft\Annotation\Annotation\Parser\Parser;

class DistributedLockParserTest extends TestCase
{
    public function testParser()
    {
        $reflectClass = new ReflectionClass(__CLASS__);
        $lock = new DistributedLock([]);
        
        $parser = new DistributedLockParser(__CLASS__, $reflectClass, []);
        $parser->setMethodName(__METHOD__);
        
        $this->assertSame([], $parser->parse(Parser::TYPE_METHOD, $lock));
        
        $this->assertSame($lock, DistributedLockRegister::getLock(__CLASS__, __METHOD__));
        return [];
    }
}
