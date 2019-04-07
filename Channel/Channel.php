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

use function iter\flatten;
use function iter\fn\method;
use function iter\map;
use function iter\toArray;
use OutOfBoundsException;
use RuntimeException;
use Webmozart\Assert\Assert;

class Channel
{
    public const DEFAULT_NAME = 'messages';

    /**
     * @var string
     */
    private $name;

    /**
     * @var Partition[]
     */
    private $partitions = [];

    public function __construct(string $name, array $partitions)
    {
        Assert::stringNotEmpty($name);
        Assert::allIsInstanceOf($partitions, Partition::class);

        $this->name = $name;

        foreach ($partitions as $partition) {
            $this->partitions[$partition->getName()] = $partition;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Partition[]
     */
    public function getPartitions(): array
    {
        return $this->partitions;
    }

    public function isPartitioned(): bool
    {
        return count($this->partitions) > 0;
    }

    public function getPartition(string $partitionName): Partition
    {
        if (!isset($this->partitions[$partitionName])) {
            throw new OutOfBoundsException(sprintf(
                'The partition "%s" does not exist in channel "%s".',
                $partitionName,
                $this->name
            ));
        }

        return $this->partitions[$partitionName];
    }

    public function hasPartition(string $partitionName): bool
    {
        return isset($this->partitions[$partitionName]);
    }

    public function findPartitionMatchingRoutingKey(string $routingKey): Partition
    {
        foreach ($this->partitions as $partition) {
            foreach ($partition->getRoutingKeys() as $routingKeyPattern) {
                if ($this->routingKeyMatches($routingKey, $routingKeyPattern)) {
                    return $partition;
                }
            }
        }

        throw new RuntimeException(sprintf(
            'Did not find a matching partition for channel "%s" and '.
            'routing key "%s". Available patterns: "%s"',
            $this->name,
            $routingKey,
            implode('", "', toArray(flatten(map(method('getRoutingKeys'), $this->partitions))))
        ));
    }

    private function routingKeyMatches(string $routingKey, string $pattern): bool
    {
        $regex = '/^'.str_replace('\\*', '.*', preg_quote($pattern, '/')).'$/';

        return (bool) preg_match($regex, $routingKey);
    }
}
