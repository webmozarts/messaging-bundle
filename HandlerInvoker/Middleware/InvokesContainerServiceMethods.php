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

use Webmozarts\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
use Webmozarts\MessagingBundle\HandlerDescriptor\ServiceMethodHandlerDescriptor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

class InvokesContainerServiceMethods implements HandlerInvokerMiddleware
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function invokeHandler($message, HandlerDescriptor $handlerDescriptor, callable $next): void
    {
        Assert::isInstanceOf($handlerDescriptor, ServiceMethodHandlerDescriptor::class);

        /** @var ServiceMethodHandlerDescriptor $handlerDescriptor */
        $service = $this->container->get($handlerDescriptor->getServiceName());

        call_user_func([$service, $handlerDescriptor->getMethodName()], $message, $handlerDescriptor->getMetadata());

        $next($message, $handlerDescriptor);
    }
}
