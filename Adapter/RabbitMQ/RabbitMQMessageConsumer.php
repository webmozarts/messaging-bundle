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

namespace Cwd\MessagingBundle\Adapter\RabbitMQ;

use Cwd\MessagingBundle\Channel\Channel;
use Cwd\MessagingBundle\Channel\Partition;
use InvalidArgumentException;
use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use SimpleBus\Asynchronous\Consumer\SerializedEnvelopeConsumer;
use function iter\fn\method;

class RabbitMQMessageConsumer extends Consumer
{
    public function __construct(AbstractConnection $conn, SerializedEnvelopeConsumer $serializedEnvelopeConsumer)
    {
        parent::__construct($conn);

        $this->setCallback(
            function (AMQPMessage $msg) use ($serializedEnvelopeConsumer) {
                $serializedEnvelopeConsumer->consume($msg->body);

                return ConsumerInterface::MSG_ACK;
            }
        );
    }

    public function selectChannel(Channel $channel, Partition $partition = null): void
    {
        $this->setExchangeOptions($this->getExchangeOptions($channel));
        $this->setQueueOptions($this->getQueueOptions($channel, $partition));
    }

    private function getExchangeOptions(Channel $channel): array
    {
        return [
            'name' => $channel->getName(),
            'type' => $channel->isPartitioned() ? 'topic' : 'direct',
        ];
    }

    private function getQueueOptions(Channel $channel, Partition $partition = null): array
    {
        if (null === $partition && $channel->isPartitioned()) {
            throw new InvalidArgumentException(sprintf(
                'A partition must be provided for partitioned channels. '.
                'Available partitions: "%s"',
                implode('", "', array_map(method('getName'), $channel->getPartitions()))
            ));
        }

        if (null !== $partition) {
            return [
                'name' => $channel->getName().'_'.$partition->getName(),
                'routing_keys' => $partition->getRoutingKeys(),
            ];
        }

        return ['name' => $channel->getName()];
    }
}
