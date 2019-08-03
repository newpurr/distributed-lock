<?php

namespace Happysir\Lock\Annotation\Mapping;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class DistributedLock
 *
 * @Annotation
 * @Target("METHOD")
 * @Attributes({
 *     @Attribute("key", type="string"),
 *     @Attribute("rate", type="int"),
 *     @Attribute("max", type="int"),
 * })
 */
class DistributedLock
{
    /**
     * name
     *
     * @var string
     */
    protected $key;
    
    /**
     * DistributedLock constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->key = $values['key'] = $values['value'];
            
            unset($values['value']);
        }
        
        if (isset($values['key'])) {
            $this->key = $values['key'];
        }
    }
    
    /**
     * getKey
     *
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }
}
