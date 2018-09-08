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

namespace Cwd\MessagingBundle\HandlerInvoker\Middleware;

use Cwd\DataBundle\Id\Identifiable;
use Cwd\MessagingBundle\Annotation\MetadataResolver\RunAsMetadataResolver;
use Cwd\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
use Cwd\MessagingBundle\Message\WrappedMessageWithMetadata;
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
            $this->messages[] = $user instanceof Identifiable
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
