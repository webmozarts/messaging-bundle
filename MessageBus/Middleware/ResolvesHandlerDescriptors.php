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

use Webmozarts\MessagingBundle\HandlerDescriptor\DescriptorResolver\HandlerDescriptorResolver;
use Webmozarts\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
use Webmozarts\MessagingBundle\Message\WrappedMessageWithMetadata;
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
