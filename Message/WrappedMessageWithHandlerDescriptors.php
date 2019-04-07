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

namespace Webmozarts\MessagingBundle\Message;

use Webmozarts\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
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
