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
final class RoutingKey
{
    /**
     * @var string
     */
    private $expression;

    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $data['expression'] = $data['value'];
            unset($data['value']);
        }

        foreach ($data as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }
}
