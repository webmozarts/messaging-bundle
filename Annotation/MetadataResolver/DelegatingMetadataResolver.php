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
