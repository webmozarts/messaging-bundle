<?php

namespace Webmozarts\MessagingBundle\ErrorLogger;

use Throwable;

interface ErrorLogger
{
    public function logError(Throwable $throwable): void;
}
