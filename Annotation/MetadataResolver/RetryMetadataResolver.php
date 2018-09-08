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

use Cwd\MessagingBundle\Annotation\Retry;

class RetryMetadataResolver implements AnnotationMetadataResolver
{
    public const TIMES = 'retry.times';

    public const FAILED_TIMES = 'retry.failed_times';

    public const ON_EXCEPTION = 'retry.on_exception';

    public const RETRY = 'retry.retry';

    public const DISCARD = 'retry.discard';

    public function resolveMetadata($annotation): array
    {
        return $annotation instanceof Retry
            ? [self::TIMES => $annotation->getTimes(), self::ON_EXCEPTION => $annotation->getOnException()]
            : [];
    }
}
