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

namespace Webmozarts\MessagingBundle\Annotation;

use Webmozart\Assert\Assert;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Partitions
{
    /**
     * @var Partition[]
     */
    private $partitions;

    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $data['partitions'] = $data['value'];
            unset($data['value']);
        }

        Assert::allIsInstanceOf($data['partitions'], Partition::class);

        $this->partitions = $data['partitions'];
    }

    /**
     * @return Partition[]
     */
    public function getPartitions(): array
    {
        return $this->partitions;
    }
}
