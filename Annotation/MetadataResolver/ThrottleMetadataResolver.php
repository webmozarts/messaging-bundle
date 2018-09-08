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

namespace Cwd\MessagingBundle\Annotation\MetadataResolver;

use Cwd\MessagingBundle\Annotation\Throttle;

class ThrottleMetadataResolver implements AnnotationMetadataResolver
{
    public const INTERVAL = 'throttle.interval';

    public function resolveMetadata($annotation): array
    {
        return $annotation instanceof Throttle
            ? [self::INTERVAL => $annotation->getInterval()]
            : [];
    }
}
