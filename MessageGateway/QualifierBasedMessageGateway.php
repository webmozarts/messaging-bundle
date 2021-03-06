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

namespace Webmozarts\MessagingBundle\MessageGateway;

use Webmozarts\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

class QualifierBasedMessageGateway implements MessageGateway
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string[]
     */
    private $serviceNamesByQualifier;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerQualifier(string $qualifier, string $serviceName): void
    {
        $this->serviceNamesByQualifier[$qualifier] = $serviceName;
    }

    public function handle($message): void
    {
        /* @var WrappedMessageWithHandlerDescriptors $message */
        Assert::isInstanceOf($message, WrappedMessageWithHandlerDescriptors::class);

        foreach ($this->serviceNamesByQualifier as $qualifier => $serviceName) {
            if ($message->getMessage() instanceof $qualifier) {
                $this->container->get($serviceName)->handle($message);

                return;
            }
        }

        throw new RuntimeException(sprintf(
            'Did not find a known qualifier on event of type %s.',
            get_class($message->getMessage())
        ));
    }
}
