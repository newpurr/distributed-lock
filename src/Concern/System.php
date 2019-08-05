<?php

namespace Happysir\Lock\Concern;

use Throwable;

/**
 * Trait System
 *
 * @version 1.0
 * @package \Happysir\Lock\Concern
 */
trait System
{
    /**
     * get swoole work id
     *
     * @return int
     */
    public function getWorkId() : int
    {
        try {
            $server = server();
    
            return $server ? $server->getSwooleServer()->worker_id : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }
}
