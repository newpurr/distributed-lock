[TOC]
### 锁
加锁是访问临界资源时对资源的保护机制，加锁的目的是对并发访问和并发请求进行保护，未获取到锁的请求将无权操作资源。
> 临界区域指的是一块对公共资源进行访问的代码，并非一种机制或是算法。一个程序、进程、线程可以拥有多个临界区域

### 安装
```shell
composer require happysir/distributed-lock
```

### 配置
```php
use Happysir\Lock\RedisLock;
use Swoft\Redis\Pool;
use Swoft\Redis\RedisDb;

return [
    // redis db
    'redis'            => [
        'class'    => RedisDb::class,
        'host'     => '127.0.0.1',
        'port'     => 6379,
        'database' => 0,
        'option'   => [
            'prefix'     => 'swoft:',
            'serializer' => Redis::SERIALIZER_NONE
        ]
    ],
    // redis pool
    Pool::DEFAULT_POOL => [
        'class'       => Pool::class,
        'redisDb'     => \bean('redis'),
        'minActive'   => 10,
        'maxActive'   => 20,
        'maxWait'     => 0,
        'maxWaitTime' => 0,
        'maxIdleTime' => 60,
    ],
    // redis lock
    RedisLock::class   => [
        // redis pool
        'pool' => Pool::DEFAULT_POOL,
    ]
];
```

详细参数：
- `pool` Redis 连接池 

### 使用
#### 注解方式
当前组件提供了注解的方式使用分布式锁，使用方式如下：
```php
<?php declare(strict_types=1);

namespace App\Http\Controller;

use Happysir\Lock\Annotation\Mapping\DistributedLock;
use Swoft\Context\Context;
use Swoft\Http\Message\Request;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Router\Router;
use Swoft\View\Renderer;
use Swoole\Coroutine;
use Throwable;

/**
 * Class HomeController
 * @Controller()
 */
class HomeController
{
    /**
     * @RequestMapping("/")
     * @DistributedLock(key="request.getUriPath()~':'~request.query('id')",ttl=6,type=DistributedLock::RETRY_TO_GET)
     * @throws Throwable
     */
    public function index(Request $request): Response
    {
        Coroutine::sleep(1);
        
        return context()->getResponse();
    }

    /**
     * @RequestMapping("/hello")
     * @DistributedLock(key="hello~':'~request.query('id')",ttl=6,type=DistributedLock::NON_BLOCKING)
     * @throws Throwable
     */
    public function hello(Request $request): Response
    {
        Coroutine::sleep(1);
        
        return context()->getResponse();
    }
}
````

> `@DistributedLock`注解说明

- `key` 锁资源唯一标识key，同一时间片只能由一个线程持有
- `type` 锁类型 
    - `DistributedLock::NON_BLOCKING`类型，在获取锁失败时会直接抛出`DistributedLockException`异常
    - `DistributedLock::RETRY_TO_GET`类型，在获取锁失败时，会重新尝试获取锁（每0.5s重试一次，最多重试retries次），超过最大重试次数后会抛出`DistributedLockException`异常
- `ttl` 锁的有效时间（ttls后锁会自动过期）
- `errcode` 尝试持有锁时抛出的异常对应的code
- `errmsg` 尝试持有锁时抛出的异常对应的msg
- `retries` 重试次数(需要注意的是，重试次数过多，可能会造成系统负载上升，因此硬性限制最大重试次数为10次)

> key 这里支持 symfony/expression-language 表达式，可以实现很多复杂的功能，[详细文档](http://www.symfonychina.com/doc/current/components/expression_language/syntax.html)。key 表达式内置 CLASS(类名) 和 METHOD(方法名称) 两个变量，方便开发者使用。详细使用参考Swoft[服务限流](https://www.swoft.org/docs/2.x/zh-CN/ms/govern/limiter.html#%E4%BD%BF%E7%94%A8)章节

#### 代码方式(显式使用)
##### `DistributedLock::NON_BLOCKING`
```php
use Happysir\Lock\RedisLock;
$distributedLock = bean(RedisLock::class);

if (!$distributedLock->tryLock('test', 1)) {
    return false;
}

// 业务逻辑...

$distributedLock->unLock();
```
##### `DistributedLock::RETRY_TO_GET`
```php
use Happysir\Lock\RedisLock;
$distributedLock = bean(RedisLock::class);

// 加锁1s，重试3次
if (!$distributedLock->lock('test', 1, 3)) {
    return false;
}

// 业务逻辑...

$distributedLock->unLock();
```

### 看门狗机制
存在这样一种场景，业务代码较为复杂，执行时长会超过我们申请锁的时间，这时候就可能有第二个线程成功申请锁，未达到我们想要的保护目的。

针对这样的场景，我们在组件中支持了锁自动续约机制-看门狗任务。

当我们成功获取锁后，程序会为我们创建一个协程，此协程的作用是续约我们的锁的过期时间。在锁被释放或者当前请求结束后，此协程会自动退出。
