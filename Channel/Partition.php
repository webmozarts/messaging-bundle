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

namespace Webmozarts\MessagingBundle\Channel;

use Webmozart\Assert\Assert;

class Partition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $routingKeys = [];

    public function __construct(string $name, array $routingKeys)
    {
        Assert::stringNotEmpty($name);
        Assert::allStringNotEmpty($routingKeys);

        $this->name = $name;
        $this->routingKeys = $routingKeys;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getRoutingKeys(): array
    {
        return $this->routingKeys;
    }
}
