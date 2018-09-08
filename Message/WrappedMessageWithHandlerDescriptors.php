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

namespace Cwd\MessagingBundle\Message;

use Cwd\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
use Webmozart\Assert\Assert;

class WrappedMessageWithHandlerDescriptors
{
    /**
     * @var object
     */
    private $message;

    /**
     * @var HandlerDescriptor[]
     */
    private $handlerDescriptors;

    public function __construct($message, array $handlerDescriptors)
    {
        Assert::object($message);
        Assert::allIsInstanceOf($handlerDescriptors, HandlerDescriptor::class);

        $this->message = $message;
        $this->handlerDescriptors = $handlerDescriptors;
    }

    /**
     * @return object
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return HandlerDescriptor[]
     */
    public function getHandlerDescriptors(): array
    {
        return $this->handlerDescriptors;
    }

    public function withHandlerDescriptors(array $handlerDescriptors): self
    {
        Assert::allIsInstanceOf($handlerDescriptors, HandlerDescriptor::class);

        return new self($this->message, $handlerDescriptors);
    }
}
