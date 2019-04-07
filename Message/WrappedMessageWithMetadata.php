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

use Webmozart\Assert\Assert;

class WrappedMessageWithMetadata
{
    /**
     * @var object
     */
    private $message;

    /**
     * @var array
     */
    private $metadata;

    public function __construct($message, array $metadata)
    {
        Assert::object($message);

        $this->message = $message;
        $this->metadata = $metadata;
    }

    /**
     * @return object
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function withMetadata(array $metadata): self
    {
        return new static($this->message, array_filter(array_replace($this->metadata, $metadata)));
    }
}
