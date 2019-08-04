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
            if ($server === null) {
                return 0;
            }
    
            return $server->getSwooleServer()->worker_id;
        } catch (Throwable $e) {
            return 0;
        }
    }
}
