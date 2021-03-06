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
final class Channel
{
    /**
     * @var string
     */
    private $name;

    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $data['name'] = $data['value'];
            unset($data['value']);
        }

        foreach ($data as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
