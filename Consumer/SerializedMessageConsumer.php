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

namespace Webmozarts\MessagingBundle\Consumer;

use Webmozarts\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
use Webmozarts\MessagingBundle\MessageGateway\MessageGateway;
use SimpleBus\Asynchronous\Consumer\SerializedEnvelopeConsumer;
use Symfony\Component\Serializer\SerializerInterface;

class SerializedMessageConsumer implements SerializedEnvelopeConsumer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var MessageGateway
     */
    private $messageGateway;

    public function __construct(SerializerInterface $serializer, MessageGateway $messageGateway)
    {
        $this->serializer = $serializer;
        $this->messageGateway = $messageGateway;
    }

    public function consume($serializedEnvelope): void
    {
        $this->messageGateway->handle(
            $this->serializer->deserialize($serializedEnvelope, WrappedMessageWithHandlerDescriptors::class, 'json')
        );
    }
}
