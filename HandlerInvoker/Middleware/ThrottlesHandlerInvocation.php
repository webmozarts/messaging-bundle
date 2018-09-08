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

use Cwd\MessagingBundle\Annotation\MetadataResolver\ThrottleMetadataResolver;
use Cwd\MessagingBundle\HandlerDescriptor\HandlerDescriptor;

class ThrottlesHandlerInvocation implements HandlerInvokerMiddleware
{
    /**
     * @var string
     */
    private $usleep;

    /**
     * @var int
     */
    private $lastCallTimeSec = 0;

    public function __construct($usleep = 'usleep')
    {
        $this->usleep = $usleep;
    }

    public function invokeHandler($message, HandlerDescriptor $handlerDescriptor, callable $next): void
    {
        $intervalMs = $handlerDescriptor->getMetadata()[ThrottleMetadataResolver::INTERVAL] ?? 0;

        if ($intervalMs > 0) {
            $currentTimeSec = microtime(true);
            $diff = round(1000 * ($currentTimeSec - $this->lastCallTimeSec));

            // Throttle the API calls
            if ($diff < $intervalMs) {
                call_user_func($this->usleep, (int) (1000 * ($intervalMs - $diff)));
            }

            $this->lastCallTimeSec = $currentTimeSec;
        }

        $next($message, $handlerDescriptor);
    }
}
