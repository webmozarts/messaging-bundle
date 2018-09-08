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

namespace Cwd\MessagingBundle\Channel\Registry;

use InvalidArgumentException;
use Cwd\MessagingBundle\Channel\Channel;
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
