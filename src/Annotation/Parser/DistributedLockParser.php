<?php declare(strict_types = 1);

namespace Swoft\Limiter\Annotation\Parser;

use Happysir\Lock\Annotation\Mapping\DistributedLock;
use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;

/**
 * Class RateLimiterParser
 *
 * @since 2.0
 *
 * @AnnotationParser(DistributedLock::class)
 */
class DistributedLockParser extends Parser
{
    /**
     * @param int             $type
     * @param DistributedLock $annotationObject
     *
     * @return array
     */
    public function parse(int $type, $annotationObject) : array
    {
        return [];
    }
}
