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

use Cwd\MessagingBundle\HandlerDescriptor\DescriptorResolver\HandlerDescriptorResolver;
use Cwd\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
use Cwd\MessagingBundle\Message\WrappedMessageWithMetadata;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class ResolvesHandlerDescriptors implements MessageBusMiddleware
{
    /**
     * @var HandlerDescriptorResolver
     */
    private $handlerDescriptorResolver;

    public function __construct(HandlerDescriptorResolver $handlerDescriptorResolver)
    {
        $this->handlerDescriptorResolver = $handlerDescriptorResolver;
    }

    public function handle($message, callable $next): void
    {
        $handlerDescriptors = $this->handlerDescriptorResolver->resolveHandlerDescriptors($message);

        // Metadata is now contained within the handler descriptors
        if ($message instanceof WrappedMessageWithMetadata) {
            $message = $message->getMessage();
        }

        $next(new WrappedMessageWithHandlerDescriptors($message, $handlerDescriptors));
    }
}
