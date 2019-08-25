<?php

namespace Happysir\Lock\Unit;

use PHPUnit\Framework\TestCase;

class SwooleRandomIntest extends TestCase
{
    /**
     * testGenerate
     */
    public function testGenerate() : void
    {
        try {
            $this->assertNotEmpty(swoole_random_int(1, 100));
        } catch (\Throwable $e) {
            $this->assertNull($e);
        }
    }
}
