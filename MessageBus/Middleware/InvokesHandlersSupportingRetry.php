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

use Webmozarts\MessagingBundle\Annotation\MetadataResolver\RetryMetadataResolver;
use Webmozarts\MessagingBundle\HandlerInvoker\HandlerInvoker;
use Webmozarts\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Raven_Client;
use SimpleBus\Asynchronous\Publisher\Publisher;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use Webmozart\Assert\Assert;

class InvokesHandlersSupportingRetry implements MessageBusMiddleware
{
    /**
     * @var HandlerInvoker
     */
    private $handlerInvoker;

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var Raven_Client|null
     */
    private $sentryClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $level;

    public function __construct(
        HandlerInvoker $handlerInvoker,
        Publisher $publisher,
        Raven_Client $sentryClient = null,
        LoggerInterface $logger = null,
        $level = LogLevel::DEBUG
    ) {
        if (null === $logger) {
            $logger = new NullLogger();
        }

        $this->handlerInvoker = $handlerInvoker;
        $this->publisher = $publisher;
        $this->sentryClient = $sentryClient;
        $this->logger = $logger;
        $this->level = $level;
    }

    public function handle($message, callable $next): void
    {
        /* @var WrappedMessageWithHandlerDescriptors $message */
        Assert::isInstanceOf($message, WrappedMessageWithHandlerDescriptors::class);

        $unwrappedMessage = $message->getMessage();
        $publishedHandlerDescriptors = [];

        foreach ($message->getHandlerDescriptors() as $handlerDescriptor) {
            try {
                $this->logger->log($this->level, 'Started invoking a handler', [
                    'message' => $unwrappedMessage,
                    'handler' => $handlerDescriptor,
                ]);

                $this->handlerInvoker->invokeHandler($unwrappedMessage, $handlerDescriptor);

                $this->logger->log($this->level, 'Finished invoking a handler', [
                    'message' => $unwrappedMessage,
                    'handler' => $handlerDescriptor,
                ]);
            } catch (Exception $e) {
                $metadata = $handlerDescriptor->getMetadata();
                $retryTimes = $metadata[RetryMetadataResolver::TIMES] ?? 0;
                $failedTimes = $metadata[RetryMetadataResolver::FAILED_TIMES] ?? 0;
                $exceptionClass = $metadata[RetryMetadataResolver::ON_EXCEPTION] ?? null;

                $this->logger->log($this->level, 'Failed invoking a handler', [
                    'message' => $unwrappedMessage,
                    'handler' => $handlerDescriptor,
                    'exception' => $e,
                ]);

                ++$failedTimes;

                if ($failedTimes <= $retryTimes && get_class($e) === $exceptionClass) {
                    $publishedHandlerDescriptors[] = $handlerDescriptor->withMetadata([
                        RetryMetadataResolver::RETRY => true,
                    ]);

                    $this->logger->log($this->level, 'Marking handler for retry', [
                        'message' => $unwrappedMessage,
                        'handler' => $handlerDescriptor,
                        'exception' => $e,
                        'failed_times' => $failedTimes,
                        'retry_times' => $retryTimes,
                    ]);
                } else {
                    $publishedHandlerDescriptors[] = $handlerDescriptor->withMetadata([
                        RetryMetadataResolver::DISCARD => true,
                    ]);

                    $this->logger->log($this->level, 'Discarding handler: Too many failed attempts', [
                        'message' => $unwrappedMessage,
                        'handler' => $handlerDescriptor,
                        'exception' => $e,
                        'failed_times' => $failedTimes,
                        'retry_times' => $retryTimes,
                    ]);
                }

                if (null !== $this->sentryClient) {
                    $this->sentryClient->captureException($e);
                }
            }
        }

        if (count($publishedHandlerDescriptors) > 0) {
            $this->publisher->publish($message->withHandlerDescriptors($publishedHandlerDescriptors));
        }

        $next($message);
    }
}
