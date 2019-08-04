<?php declare(strict_types = 1);

namespace Happysir\Lock\Annotation\Parser;

use Happysir\Lock\Annotation\Mapping\DistributedLock;
use Happysir\Lock\DistributedLockRegister;
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
        if ($type !== self::TYPE_METHOD) {
            return [];
        }
        
        DistributedLockRegister::registerLock($this->className, $this->methodName, $annotationObject);
        
        return [];
    }
}
