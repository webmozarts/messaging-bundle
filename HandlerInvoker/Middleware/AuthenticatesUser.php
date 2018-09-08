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

use Cwd\MessagingBundle\Annotation\MetadataResolver\RunAsMetadataResolver;
use Cwd\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
use Cwd\MessagingBundle\UserProvider\UserByStringIdProvider;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticatesUser implements HandlerInvokerMiddleware
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserByStringIdProvider
     */
    private $userProvider;

    /**
     * @var string
     */
    private $anonymousUserId;

    public function __construct(TokenStorageInterface $tokenStorage, UserByStringIdProvider $userProvider, string $anonymousUserId = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
        $this->anonymousUserId = $anonymousUserId;
    }

    public function invokeHandler($message, HandlerDescriptor $handlerDescriptor, callable $next): void
    {
        $userId = $handlerDescriptor->getMetadata()[RunAsMetadataResolver::USER_ID] ?? null;
        $token = $this->tokenStorage->getToken();
        $isUserAuthenticated = null !== $token && !$token instanceof AnonymousToken;

        if (null === $userId && !$isUserAuthenticated) {
            $userId = $this->anonymousUserId;
        }

        if (null !== $userId) {
            $user = $this->userProvider->loadUserByStringId($userId);
            $previousToken = $this->logInAs($user);

            try {
                $next($message, $handlerDescriptor);
            } finally {
                $this->restoreLogin($previousToken);
            }

            return;
        }

        // Without login switching
        $next($message, $handlerDescriptor);
    }

    private function logInAs(UserInterface $user): ?TokenInterface
    {
        $previousToken = $this->tokenStorage->getToken();

        $this->tokenStorage->setToken(new PreAuthenticatedToken(
            $user,
            null,
            'fos_userbundle',
            $user->getRoles()
        ));

        return $previousToken;
    }

    protected function restoreLogin(?TokenInterface $token): void
    {
        $this->tokenStorage->setToken($token);
    }
}
