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

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Retry
{
    /**
     * @var int
     */
    private $times;

    /**
     * @var string
     */
    private $onException;

    public function __construct(array $data)
    {
        foreach ($data as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * @return int
     */
    public function getTimes(): int
    {
        return $this->times;
    }

    /**
     * @return string
     */
    public function getOnException(): string
    {
        return $this->onException;
    }
}
