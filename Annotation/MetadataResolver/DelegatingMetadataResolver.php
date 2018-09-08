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

use Webmozart\Assert\Assert;

class DelegatingMetadataResolver implements AnnotationMetadataResolver
{
    /**
     * @var AnnotationMetadataResolver[]
     */
    private $resolvers;

    public function __construct(array $resolvers)
    {
        Assert::allIsInstanceOf($resolvers, AnnotationMetadataResolver::class);

        $this->resolvers = $resolvers;
    }

    public function resolveMetadata($annotation): array
    {
        $metadata = [];

        foreach ($this->resolvers as $resolver) {
            foreach ($resolver->resolveMetadata($annotation) as $key => $value) {
                $metadata[$key] = $value;
            }
        }

        return $metadata;
    }
}
