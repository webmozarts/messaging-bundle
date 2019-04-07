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

namespace Webmozarts\MessagingBundle\Channel\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Webmozarts\MessagingBundle\Channel\Channel;
use Webmozarts\MessagingBundle\Channel\Partition;
use ReflectionClass;
use ReflectionMethod;
use Webmozarts\MessagingBundle\Annotation\Channel as ChannelAnnotation;
use Webmozarts\MessagingBundle\Annotation\Partitions as PartitionsAnnotation;
use Webmozarts\MessagingBundle\Annotation\Partition as PartitionAnnotation;

class AnnotationBasedChannelFactory
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function createChannelForHandlerMethod(ReflectionMethod $method): Channel
    {
        $annotations = $this->collectMethodAnnotations($method);

        /** @var ChannelAnnotation $channelAnnotation */
        $channelAnnotation = $annotations[ChannelAnnotation::class] ?? new ChannelAnnotation(['name' => Channel::DEFAULT_NAME]);
        /** @var PartitionsAnnotation $partitionsAnnotation */
        $partitionsAnnotation = $annotations[PartitionsAnnotation::class] ?? null;

        return new Channel(
            $channelAnnotation->getName(),
            null !== $partitionsAnnotation
                ? array_map([$this, 'createPartition'], $partitionsAnnotation->getPartitions())
                : []
        );
    }

    private function collectMethodAnnotations(ReflectionMethod $method): array
    {
        $annotations = [];

        // Lowest priority: Message type
        if ($method->getNumberOfParameters() > 0) {
            $messageType = $method->getParameters()[0]->getClass();

            if ($messageType instanceof ReflectionClass) {
                foreach ($this->collectClassAnnotations($messageType) as $key => $value) {
                    $annotations[$key] = $value;
                }
            }
        }

        // Medium priority: Method defining class
        foreach ($this->collectClassAnnotations($method->getDeclaringClass()) as $key => $value) {
            $annotations[$key] = $value;
        }

        // Highest priority: Method itself
        return $this->mergeAnnotations(
            $annotations,
            $this->annotationReader->getMethodAnnotation($method, ChannelAnnotation::class),
            $this->annotationReader->getMethodAnnotation($method, PartitionsAnnotation::class)
        );
    }

    private function collectClassAnnotations(ReflectionClass $class): array
    {
        $annotations = [];

        $parentClass = $class->getParentClass();
        $interfaces = $class->getInterfaces();

        if (false !== $parentClass) {
            $annotations = $this->collectClassAnnotations($parentClass);
        }

        foreach ($interfaces as $interface) {
            foreach ($this->collectClassAnnotations($interface) as $name => $annotation) {
                $annotations[$name] = $annotation;
            }
        }

        return $this->mergeAnnotations(
            $annotations,
            $this->annotationReader->getClassAnnotation($class, ChannelAnnotation::class),
            $this->annotationReader->getClassAnnotation($class, PartitionsAnnotation::class)
        );
    }

    private function mergeAnnotations(array $annotations, $channelAnnotation, $partitionsAnnotation): array
    {
        if (null !== $channelAnnotation) {
            $annotations[ChannelAnnotation::class] = $channelAnnotation;
        }

        if (null !== $partitionsAnnotation) {
            $annotations[PartitionsAnnotation::class] = $partitionsAnnotation;
        }

        return $annotations;
    }

    private function createPartition(PartitionAnnotation $annotation): Partition
    {
        return new Partition($annotation->getName(), $annotation->getRoutingKeys());
    }
}
