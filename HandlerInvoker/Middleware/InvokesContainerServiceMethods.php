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

use Cwd\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
use Cwd\MessagingBundle\HandlerDescriptor\ServiceMethodHandlerDescriptor;
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
