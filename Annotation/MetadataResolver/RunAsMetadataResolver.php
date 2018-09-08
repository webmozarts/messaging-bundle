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

use Cwd\MessagingBundle\Annotation\RunAs;

class RunAsMetadataResolver implements AnnotationMetadataResolver
{
    public const USER_ID = 'run_as.user_id';

    public function resolveMetadata($annotation): array
    {
        return $annotation instanceof RunAs
            ? [self::USER_ID => $annotation->getUserId()]
            : [];
    }
}