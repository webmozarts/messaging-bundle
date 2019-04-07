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

namespace Webmozarts\MessagingBundle\Channel\Registry;

use InvalidArgumentException;
use Webmozarts\MessagingBundle\Channel\Channel;
use OutOfBoundsException;
use Webmozart\Assert\Assert;

class ChannelRegistry
{
    /**
     * @var Channel[]
     */
    private $channels;

    public function __construct(array $channels = [])
    {
        Assert::allIsInstanceOf($channels, Channel::class);

        foreach ($channels as $channel) {
            $this->addChannel($channel);
        }
    }

    public function addChannel(Channel $channel): void
    {
        if (isset($this->channels[$channel->getName()])) {
            throw new InvalidArgumentException(sprintf(
                'A channel named "%s" is already defined.',
                $channel->getName()
            ));
        }

        $this->channels[$channel->getName()] = $channel;
    }

    public function getChannel(string $name): Channel
    {
        if (!isset($this->channels[$name])) {
            throw new OutOfBoundsException(sprintf(
                'The channel named "%s" does not exist.',
                $name
            ));
        }

        return $this->channels[$name];
    }

    public function hasChannel(string $name): bool
    {
        return isset($this->channels[$name]);
    }

    /**
     * @return Channel[]
     */
    public function getChannels(): array
    {
        return $this->channels;
    }
}
