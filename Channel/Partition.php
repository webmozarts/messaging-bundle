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

namespace Cwd\MessagingBundle\Channel;

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
