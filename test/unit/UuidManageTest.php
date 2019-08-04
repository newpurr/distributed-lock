<?php

namespace Happysir\Lock\Unit;

use Happysir\Lock\Bean\UuidManage;
use PHPUnit\Framework\TestCase;

class UuidManageTest extends TestCase
{
    public function testGenerate()
    {
        $this->assertNotEmpty((new UuidManage)->generate());
    }
}
