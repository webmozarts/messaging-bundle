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

namespace Cwd\MessagingBundle\Consumer;

use Cwd\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
use Cwd\MessagingBundle\MessageGateway\MessageGateway;
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
