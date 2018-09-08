<?php

/*
 * This file is part of the ÖWM API.
 *
 * (c) 2016-2018 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Cwd\MessagingBundle\MessageBus\Middleware;

use Cwd\MessagingBundle\HandlerInvoker\HandlerInvoker;
use Cwd\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
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
