<?php

/*
 * This file is part of the Webmozarts Messaging Bundle.
 *
 * (c) 2016-2019 Bernhard Schussek <bernhard.schussek@webmozarts.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Webmozarts\MessagingBundle\HandlerInvoker;

use Webmozarts\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
use Webmozarts\MessagingBundle\HandlerInvoker\Middleware\HandlerInvokerMiddleware;
use Webmozart\Assert\Assert;

class HandlerInvokerSupportingMiddleware implements HandlerInvoker
{
    /**
     * @var HandlerInvokerMiddleware[]
     */
    private $middlewares;

    public function __construct(array $middlewares)
    {
        Assert::allIsInstanceOf($middlewares, HandlerInvokerMiddleware::class);

        $this->middlewares = $middlewares;
    }

    public function invokeHandler($message, HandlerDescriptor $handlerDescriptor): void
    {
        call_user_func($this->callableForNextMiddleware(0), $message, $handlerDescriptor);
    }

    private function callableForNextMiddleware($index): callable
    {
        if (!isset($this->middlewares[$index])) {
            return function () {};
        }

        $middleware = $this->middlewares[$index];

        return function ($message, HandlerDescriptor $handlerDescriptor) use ($middleware, $index) {
            $middleware->invokeHandler(
                $message,
                $handlerDescriptor,
                $this->callableForNextMiddleware($index + 1)
            );
        };
    }
}
