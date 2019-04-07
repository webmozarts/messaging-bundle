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

namespace Webmozarts\MessagingBundle\HandlerInvoker\Middleware;

use Webmozarts\MessagingBundle\Annotation\MetadataResolver\RunAsMetadataResolver;
use Webmozarts\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
use Webmozarts\MessagingBundle\Message\WrappedMessageWithMetadata;
use SimpleBus\Message\Recorder\ContainsRecordedMessages;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CollectsEventOriginators implements ContainsRecordedMessages, HandlerInvokerMiddleware
{
    /**
     * @var ContainsRecordedMessages
     */
    private $messageRecorder;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var array
     */
    private $messages = [];

    public function __construct(ContainsRecordedMessages $messageRecorder, TokenStorageInterface $tokenStorage)
    {
        $this->messageRecorder = $messageRecorder;
        $this->tokenStorage = $tokenStorage;
    }

    public function invokeHandler($message, HandlerDescriptor $handlerDescriptor, callable $next): void
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $next($message, $handlerDescriptor);

        foreach ($this->messageRecorder->recordedMessages() as $recordedMessage) {
            $this->messages[] = method_exists($user, 'getId')
                ? new WrappedMessageWithMetadata(
                    $recordedMessage,
                    [RunAsMetadataResolver::USER_ID => (string) $user->getId()]
                )
                : $recordedMessage;
        }

        $this->messageRecorder->eraseMessages();
    }

    public function recordedMessages(): array
    {
        return $this->messages;
    }

    public function eraseMessages(): void
    {
        $this->messages = [];
    }
}
