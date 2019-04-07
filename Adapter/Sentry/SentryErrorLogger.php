<?php

namespace Webmozarts\MessagingBundle\Adapter\Sentry;

use Raven_Client;
use Throwable;
use Webmozarts\MessagingBundle\ErrorLogger\ErrorLogger;

class SentryErrorLogger implements ErrorLogger
{
    private $sentry;

    public function __construct(Raven_Client $sentry)
    {
        $this->sentry = $sentry;
    }

    public function logError(Throwable $throwable): void
    {
        $this->sentry->captureException($throwable);
    }

}
