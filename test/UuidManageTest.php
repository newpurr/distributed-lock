<?php

namespace Happysir\Lock\Test;

use Happysir\Lock\UuidManage;
use PHPUnit\Framework\TestCase;

class UuidManageTest extends TestCase
{
    public function testGenerate()
    {
        $this->assertNotEmpty((new UuidManage)->generate());
    }
}
