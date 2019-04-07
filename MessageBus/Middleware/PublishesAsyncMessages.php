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

use Webmozarts\MessagingBundle\Annotation\MetadataResolver\AsyncMetadataResolver;
use Webmozarts\MessagingBundle\Annotation\MetadataResolver\RunAsMetadataResolver;
use Webmozarts\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
use SimpleBus\Asynchronous\Publisher\Publisher;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PublishesAsyncMessages implements MessageBusMiddleware
{
    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher, TokenStorageInterface $tokenStorage)
    {
        $this->publisher = $publisher;
        $this->tokenStorage = $tokenStorage;
    }

    public function handle($message, callable $next): void
    {
        if ($message instanceof WrappedMessageWithHandlerDescriptors) {
            $immediateHandlerDescriptors = [];
            $asyncHandlerDescriptors = [];

            foreach ($message->getHandlerDescriptors() as $handlerDescriptor) {
                $metadata = $handlerDescriptor->getMetadata();

                if ($metadata[AsyncMetadataResolver::ASYNC] ?? false) {
                    if (!isset($metadata[RunAsMetadataResolver::USER_ID])) {
                        // Authenticate as the current user when processing the message
                        $handlerDescriptor = $handlerDescriptor->withMetadata([
                            RunAsMetadataResolver::USER_ID => (string) $this->getUser()->getId(),
                        ]);
                    }

                    $asyncHandlerDescriptors[] = $handlerDescriptor;
                } else {
                    $immediateHandlerDescriptors[] = $handlerDescriptor;
                }
            }

            if (count($asyncHandlerDescriptors)) {
                $this->publisher->publish($message->withHandlerDescriptors($asyncHandlerDescriptors));
            }

            $message = $message->withHandlerDescriptors($immediateHandlerDescriptors);
        }

        $next($message);
    }

    private function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
