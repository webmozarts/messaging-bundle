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

namespace Webmozarts\MessagingBundle\MetadataResolver;

use Doctrine\Common\Annotations\AnnotationReader;
use Webmozarts\MessagingBundle\Annotation\MetadataResolver\AnnotationMetadataResolver;
use ReflectionClass;
use ReflectionMethod;

class AnnotationBasedMetadataResolver implements MetadataResolver
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var AnnotationMetadataResolver
     */
    private $annotationMetadataResolver;

    /**
     * @var callable[]
     */
    private $classListeners = [];

    public function __construct(AnnotationReader $annotationReader, AnnotationMetadataResolver $annotationMetadataResolver)
    {
        $this->annotationReader = $annotationReader;
        $this->annotationMetadataResolver = $annotationMetadataResolver;
    }

    public function resolveClassMetadata(ReflectionClass $class): array
    {
        $metadata = [];

        foreach ($this->classListeners as $classListener) {
            $classListener($class);
        }

        $parentClass = $class->getParentClass();
        $interfaces = $class->getInterfaces();

        if (false !== $parentClass) {
            $metadata = $this->resolveClassMetadata($parentClass);
        }

        foreach ($interfaces as $interface) {
            foreach ($this->resolveClassMetadata($interface) as $key => $value) {
                $metadata[$key] = $value;
            }
        }

        foreach ($this->annotationReader->getClassAnnotations($class) as $annotation) {
            foreach ($this->annotationMetadataResolver->resolveMetadata($annotation) as $key => $value) {
                $metadata[$key] = $value;
            }
        }

        return $metadata;
    }

    public function resolveMethodMetadata(ReflectionMethod $method): array
    {
        $metadata = [];

        foreach ($this->annotationReader->getMethodAnnotations($method) as $annotation) {
            foreach ($this->annotationMetadataResolver->resolveMetadata($annotation) as $key => $value) {
                $metadata[$key] = $value;
            }
        }

        return $metadata;
    }

    public function addClassListener(callable $listener): void
    {
        $this->classListeners[] = $listener;
    }

    public function removeClassListener(callable $listener): void
    {
        foreach ($this->classListeners as $key => $classListener) {
            if ($classListener === $listener) {
                unset($this->classListeners[$key]);
            }
        }

        $this->classListeners = array_values($this->classListeners);
    }
}
