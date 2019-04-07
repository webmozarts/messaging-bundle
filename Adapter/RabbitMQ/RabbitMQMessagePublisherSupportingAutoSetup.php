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

namespace Webmozarts\MessagingBundle\Adapter\RabbitMQ;

use Webmozarts\MessagingBundle\Annotation\MetadataResolver\ChannelMetadataResolver;
use Webmozarts\MessagingBundle\Annotation\MetadataResolver\RetryMetadataResolver;
use Webmozarts\MessagingBundle\Channel\Channel;
use Webmozarts\MessagingBundle\Channel\Registry\ChannelRegistry;
use Webmozarts\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
use Webmozarts\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
use Webmozarts\MessagingBundle\Routing\RoutingKeyResolver;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use RuntimeException;
use SimpleBus\Asynchronous\Properties\AdditionalPropertiesResolver;
use SimpleBus\Asynchronous\Publisher\Publisher;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

class RabbitMQMessagePublisherSupportingAutoSetup implements Publisher
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var RoutingKeyResolver
     */
    private $routingKeyResolver;

    /**
     * @var AdditionalPropertiesResolver
     */
    private $additionalPropertiesResolver;

    /**
     * @var ChannelRegistry
     */
    private $channelRegistry;

    public function __construct(
        Producer $producer,
        SerializerInterface $serializer,
        RoutingKeyResolver $routingKeyResolver,
        AdditionalPropertiesResolver $additionalPropertiesResolver,
        ChannelRegistry $channelRegistry
    ) {
        $this->serializer = $serializer;
        $this->producer = $producer;
        $this->routingKeyResolver = $routingKeyResolver;
        $this->additionalPropertiesResolver = $additionalPropertiesResolver;
        $this->channelRegistry = $channelRegistry;
    }

    /**
     * Publish the given Message by serializing it and handing it over to a RabbitMQ producer.
     *
     * {@inheritdoc}
     */
    public function publish($message): void
    {
        /* @var WrappedMessageWithHandlerDescriptors $message */
        Assert::isInstanceOf($message, WrappedMessageWithHandlerDescriptors::class);

        $unwrappedMessage = $message->getMessage();
        $handlerDescriptorsByChannelProps = [];

        foreach ($message->getHandlerDescriptors() as $handlerDescriptor) {
            $channelName = $handlerDescriptor->getMetadata()[ChannelMetadataResolver::NAME] ?? Channel::DEFAULT_NAME;
            $channel = $this->channelRegistry->getChannel($channelName);
            $routingKey = $this->routingKeyResolver->resolveRoutingKey($unwrappedMessage, $handlerDescriptor);
            $exchangeOptions = $this->getExchangeOptions($channel, $handlerDescriptor);
            $queueOptions = $this->getQueueOptions($channel, $handlerDescriptor, $routingKey);
            $channelProps = serialize([$exchangeOptions, $queueOptions, $routingKey]);

            if (!isset($handlerDescriptorsByChannelProps[$channelProps])) {
                $handlerDescriptorsByChannelProps[$channelProps] = [];
            }

            $handlerDescriptorsByChannelProps[$channelProps][] = $handlerDescriptor;
        }

        foreach ($handlerDescriptorsByChannelProps as $channelProps => $handlerDescriptors) {
            [$exchangeOptions, $queueOptions, $routingKey] = unserialize($channelProps, ['allowed_classes' => false]);

            $messageForChannel = $message->withHandlerDescriptors($handlerDescriptors);
            $serializedMessage = $this->serializer->serialize($messageForChannel, 'json');
            $additionalProperties = $this->additionalPropertiesResolver->resolveAdditionalPropertiesFor($messageForChannel);

            $this->producer->setExchangeOptions($exchangeOptions);
            $this->producer->setQueueOptions($queueOptions);
            $this->producer->publish($serializedMessage, $routingKey, $additionalProperties);
        }
    }

    private function getExchangeOptions(Channel $channel, HandlerDescriptor $handlerDescriptor): array
    {
        $exchangeName = $channel->getName();

        $metadata = $handlerDescriptor->getMetadata();
        $markedRetry = $metadata[RetryMetadataResolver::RETRY] ?? false;
        $markedDiscard = $metadata[RetryMetadataResolver::DISCARD] ?? false;

        if ($markedRetry) {
            $exchangeName .= '_retry';
        } elseif ($markedDiscard) {
            $exchangeName .= '_discarded';
        }

        $options = ['name' => $exchangeName, 'type' => 'direct'];

        if (count($channel->getPartitions()) > 0) {
            $options['type'] = 'topic';
        }

        return $options;
    }

    private function getQueueOptions(Channel $channel, HandlerDescriptor $handlerDescriptor, ?string $routingKey): array
    {
        $metadata = $handlerDescriptor->getMetadata();
        $markedRetry = $metadata[RetryMetadataResolver::RETRY] ?? false;
        $markedDiscard = $metadata[RetryMetadataResolver::DISCARD] ?? false;

        if ($markedRetry) {
            return [
                'name' => $channel->getName().'_retry',
                'routing_keys' => $channel->isPartitioned() ? ['*'] : null,
                'arguments' => [
                    'x-message-ttl' => ['I', 5000],
                    'x-dead-letter-exchange' => ['S', $channel->getName()],
                ],
            ];
        }

        if ($markedDiscard) {
            return [
                'name' => $channel->getName().'_discarded',
                'routing_keys' => $channel->isPartitioned() ? ['*'] : null,
            ];
        }

        if (!$channel->isPartitioned()) {
            return ['name' => $channel->getName()];
        }

        if (null === $routingKey) {
            throw new RuntimeException(sprintf(
                'Routing keys are mandatory for partitioned channels. '.
                'Did not find a routing key for message published to channel "%s".',
                $channel->getName()
            ));
        }

        $partition = $channel->findPartitionMatchingRoutingKey($routingKey);

        return [
            'name' => $channel->getName().'_'.$partition->getName(),
            'routing_keys' => $partition->getRoutingKeys(),
        ];
    }
}
