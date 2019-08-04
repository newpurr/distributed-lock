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
 *     @Attribute("type", type="string"),
 *     @Attribute("ttl", type="int"),
 *     @Attribute("retries", type="int"),
 *     @Attribute("errcode", type="int"),
 *     @Attribute("errmsg", type="string"),
 * })
 */
class DistributedLock
{
    /**
     * non blocking to get lock
     */
    public const NON_BLOCKING = 'non_blocking';
    
    /**
     * retry to get lock
     */
    public const RETRY_TO_GET = 'retry_to_get';
    
    /**
     * key
     *
     * @var string
     */
    protected $key;
    
    /**
     * ttl
     *
     * @var int
     */
    protected $ttl = 3;
    
    /**
     * custom error code
     *
     * @var int
     */
    protected $errcode = 0;
    
    /**
     * custom error message
     *
     * @var string
     */
    protected $errmsg = '';
    
    /**
     * number of retries，only valid if the type is RETRY_TO_GET
     *
     * @var int
     */
    protected $retries = 3;
    
    /**
     * type
     *
     * @var int
     */
    protected $type = self::NON_BLOCKING;
    
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
        
        if (isset($values['ttl'])) {
            $this->ttl = $values['ttl'];
        }
        
        if (isset($values['errmsg'])) {
            $this->errmsg = $values['errmsg'];
        }
        
        if (isset($values['errcode'])) {
            $this->errcode = $values['errcode'];
        }
        
        if (isset($values['retries'])) {
            $this->retries = $values['retries'];
        }
    
        $typeArr = [
            self::NON_BLOCKING,
            self::RETRY_TO_GET
        ];
        if (isset($values['type'])
            && in_array($values['type'], $typeArr, true)) {
            $this->type = $values['type'];
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
    
    /**
     * getTtl
     *
     * @return int
     */
    public function getTtl() : int
    {
        return $this->ttl;
    }
    
    /**
     * getType
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    
    /**
     * non blocking mode acquisition lock
     *
     * @return bool
     */
    public function isNonBlocking() : bool
    {
        return $this->type === self::NON_BLOCKING;
    }
    
    /**
     * getRetries
     *
     * @return int
     */
    public function getRetries() : int
    {
        return $this->retries;
    }
    
    /**
     * getErrmsg
     *
     * @return string
     */
    public function getErrMsg() : string
    {
        return $this->errmsg;
    }
    
    /**
     * getErrcode
     *
     * @return int
     */
    public function getErrCode() : int
    {
        return $this->errcode;
    }
}
