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

namespace Cwd\MessagingBundle\HandlerDescriptor\DescriptorResolver;

use Cwd\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
use Cwd\MessagingBundle\Message\WrappedMessageWithMetadata;
use RuntimeException;
use SimpleBus\Message\Name\MessageNameResolver;

class PredefinedHandlerDescriptorResolver implements HandlerDescriptorResolver
{
    /**
     * @var MessageNameResolver
     */
    private $messageNameResolver;

    /**
     * @var HandlerDescriptor[][]
     */
    private $handlerDescriptorsByMessageName = [];

    public function __construct(MessageNameResolver $messageNameResolver)
    {
        $this->messageNameResolver = $messageNameResolver;
    }

    public function addHandlerDescriptor(string $messageName, HandlerDescriptor $handlerDescriptor): void
    {
        if (!isset($this->handlerDescriptorsByMessageName[$messageName])) {
            $this->handlerDescriptorsByMessageName[$messageName] = [];
        }

        $this->handlerDescriptorsByMessageName[$messageName][] = $handlerDescriptor;
    }

    public function resolveHandlerDescriptors($message): array
    {
        $messageMetadata = [];

        if ($message instanceof WrappedMessageWithMetadata) {
            $messageMetadata = $message->getMetadata();
            $message = $message->getMessage();
        }

        $messageName = $this->messageNameResolver->resolve($message);

        if (!isset($this->handlerDescriptorsByMessageName[$messageName])) {
            throw new RuntimeException(sprintf(
                'No handler found for message "%s".',
                $messageName
            ));
        }

        $handlerDescriptors = $this->handlerDescriptorsByMessageName[$messageName];

        if (count($messageMetadata) > 0) {
            foreach ($handlerDescriptors as $key => $handlerDescriptor) {
                $handlerDescriptors[$key] = $handlerDescriptor->withMetadata(
                    array_replace(
                        $messageMetadata,
                        $handlerDescriptor->getMetadata()
                    )
                );
            }
        }

        return $handlerDescriptors;
    }
}
