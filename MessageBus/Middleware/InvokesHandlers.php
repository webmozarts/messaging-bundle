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

namespace Webmozarts\MessagingBundle\MessageBus\Middleware;

use Webmozarts\MessagingBundle\HandlerInvoker\HandlerInvoker;
use Webmozarts\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use Webmozart\Assert\Assert;

class InvokesHandlers implements MessageBusMiddleware
{
    /**
     * @var HandlerInvoker
     */
    private $handlerInvoker;

    public function __construct(HandlerInvoker $handlerInvoker)
    {
        $this->handlerInvoker = $handlerInvoker;
    }

    public function handle($message, callable $next): void
    {
        /* @var WrappedMessageWithHandlerDescriptors $message */
        Assert::isInstanceOf($message, WrappedMessageWithHandlerDescriptors::class);

        $unwrappedMessage = $message->getMessage();

        foreach ($message->getHandlerDescriptors() as $handlerDescriptor) {
            $this->handlerInvoker->invokeHandler($unwrappedMessage, $handlerDescriptor);
        }

        $next($message);
    }
}
