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

namespace Webmozarts\MessagingBundle\Annotation\MetadataResolver;

use Webmozarts\MessagingBundle\Annotation\RoutingKey;

class RoutingKeyMetadataResolver implements AnnotationMetadataResolver
{
    public const EXPRESSION = 'routing_key.expression';

    public function resolveMetadata($annotation): array
    {
        return $annotation instanceof RoutingKey
            ? [self::EXPRESSION => $annotation->getExpression()]
            : [];
    }
}
