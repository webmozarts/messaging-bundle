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
use Oewm\Api\Application\Security\NotAuthorized;
use Oewm\Api\Application\Security\Permissions;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Ensures that the current user is authorized to run a command.
 *
 * This middleware verifies whether a user is permitted to run a command. If
 * not, a NotAuthorized exception is thrown. Otherwise, the middleware continues
 * command handling as usual.
 */
class VerifiesUserAuthorization implements HandlerInvokerMiddleware
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function invokeHandler($message, HandlerDescriptor $handlerDescriptor, callable $next): void
    {
        if (!$this->authorizationChecker->isGranted(Permissions::RUN, $message)) {
            throw NotAuthorized::toRun($message);
        }

        $next($message, $handlerDescriptor);
    }
}
