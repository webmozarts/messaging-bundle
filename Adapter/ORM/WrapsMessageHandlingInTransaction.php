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

namespace Cwd\MessagingBundle\Adapter\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Exception;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class WrapsMessageHandlingInTransaction implements MessageBusMiddleware
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var string
     */
    private $entityManagerName;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $entityManagerName
     */
    public function __construct(ManagerRegistry $managerRegistry, $entityManagerName)
    {
        $this->managerRegistry = $managerRegistry;
        $this->entityManagerName = $entityManagerName;
    }

    public function handle($message, callable $next)
    {
        /** @var $entityManager EntityManager */
        $entityManager = $this->managerRegistry->getManager($this->entityManagerName);

        try {
            $entityManager->transactional(
                function () use ($message, $next) {
                    $next($message);
                }
            );
        } catch (Exception $exception) {
            $this->managerRegistry->resetManager($this->entityManagerName);

            throw $exception;
        }
    }
}
