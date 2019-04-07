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

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Throttle
{
    /**
     * @var int
     */
    private $interval;

    public function __construct(array $data)
    {
        foreach ($data as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * @return int
     */
    public function getInterval(): int
    {
        return $this->interval;
    }
}
