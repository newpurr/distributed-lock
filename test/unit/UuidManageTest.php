<?php

namespace Happysir\Lock\Unit;

use Happysir\Lock\Bean\UuidManage;
use PHPUnit\Framework\TestCase;

class UuidManageTest extends TestCase
{
    /**
     * testGenerate
     */
    public function testGenerate() : void
    {
        $this->assertNotEmpty((new UuidManage)->generate());
    }
}
