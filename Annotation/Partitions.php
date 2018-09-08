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

namespace Cwd\MessagingBundle\Annotation;

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
