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

namespace Cwd\MessagingBundle\DependencyInjection\Compiler;

use Cwd\MessagingBundle\Channel\Channel;
use Cwd\MessagingBundle\Channel\Factory\AnnotationBasedChannelFactory;
use Cwd\MessagingBundle\Channel\Partition;
use Cwd\MessagingBundle\HandlerDescriptor\ServiceMethodHandlerDescriptor;
use Cwd\MessagingBundle\MetadataResolver\MetadataResolver;
use function iter\fn\method;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddHandlerDescriptorsPass implements CompilerPassInterface
{
    /**
     * @var MetadataResolver
     */
    private $metadataResolver;

    /**
     * @var AnnotationBasedChannelFactory
     */
    private $channelFactory;

    /**
     * @var string
     */
    private $handlerDescriptorResolverServiceName;

    /**
     * @var string
     */
    private $channelRegistryServiceName;

    /**
     * @var string
     */
    private $tagName;

    /**
     * @var string
     */
    private $methodPrefix;

    public function __construct(
        MetadataResolver $metadataResolver,
        AnnotationBasedChannelFactory $channelFactory,
        string $handlerDescriptorResolverServiceName,
        string $channelRegistryServiceName,
        string $tagName,
        string $methodPrefix
    ) {
        $this->metadataResolver = $metadataResolver;
        $this->channelFactory = $channelFactory;
        $this->handlerDescriptorResolverServiceName = $handlerDescriptorResolverServiceName;
        $this->channelRegistryServiceName = $channelRegistryServiceName;
        $this->tagName = $tagName;
        $this->methodPrefix = $methodPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $handlerDescriptorResolver = $container->getDefinition($this->handlerDescriptorResolverServiceName);
        $channelRegistry = $container->getDefinition($this->channelRegistryServiceName);

        $channelDefinitions = $channelRegistry->getArguments()[0] ?? [];
        $channelDefinitionsByName = array_combine(
            array_map(method('getArgument', [0]), $channelDefinitions),
            $channelDefinitions
        );

        $registerClassWithContainer = function (ReflectionClass $class) use ($container) {
            $container->addObjectResource($class->getName());
        };

        $registerChannel = function (ReflectionMethod $method) use (&$channelDefinitions, &$channelDefinitionsByName) {
            $methodNameWithClass = $method->getDeclaringClass()->getName().'::'.$method->getName().'()';
            $channel = $this->channelFactory->createChannelForHandlerMethod($method);
            $channelName = $channel->getName();
            $channelDefinition = $this->createServiceDefinition(
                Channel::class,
                [
                    $channelName,
                    array_map(
                        function (Partition $partition) {
                            return $this->createServiceDefinition(
                                Partition::class,
                                [$partition->getName(), $partition->getRoutingKeys()]
                            );
                        },
                        $channel->getPartitions()
                    ),
                ]
            );

            if (isset($channelDefinitionsByName[$channelName])) {
                if ($channelDefinition != $channelDefinitionsByName[$channelName]) {
                    throw new RuntimeException(sprintf(
                        'Found conflicting channel definitions for '.
                        'channel "%s" in method %s.',
                        $channelName,
                        $methodNameWithClass
                    ));
                }

                return;
            }

            $channelDefinitions[] = $channelDefinition;
            $channelDefinitionsByName[$channelName] = $channelDefinition;
        };

        $this->metadataResolver->addClassListener($registerClassWithContainer);

        foreach ($container->findTaggedServiceIds($this->tagName) as $serviceName => $tagPayloads) {
            $definition = $container->getDefinition($serviceName);
            $className = $definition->getClass();
            $class = new ReflectionClass($className);

            // Tracks this resource to trigger a container compilation when appropriate
            $container->addObjectResource($className);

            foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $methodName = $method->getName();

                if (0 !== mb_strpos($methodName, $this->methodPrefix)) {
                    continue;
                }

                $parameters = $method->getParameters();

                if (0 === count($parameters)) {
                    continue;
                }

                $messageClass = $parameters[0]->getClass();

                if (null === $messageClass) {
                    continue;
                }

                $metadata = array_replace(
                    $this->metadataResolver->resolveClassMetadata($messageClass),
                    $this->metadataResolver->resolveClassMetadata($class),
                    $this->metadataResolver->resolveMethodMetadata($method)
                );

                $handlerDescriptorResolver->addMethodCall(
                    'addHandlerDescriptor',
                    [
                        $messageClass->getName(),
                        $this->createServiceDefinition(
                            ServiceMethodHandlerDescriptor::class,
                            [$serviceName, $methodName, $metadata]
                        ),
                    ]
                );

                $registerChannel($method);
            }
        }

        $channelRegistry->setArgument(0, $channelDefinitions);

        $this->metadataResolver->removeClassListener($registerClassWithContainer);
    }

    private function createServiceDefinition(string $className, array $arguments): Definition
    {
        $definition = new Definition($className, $arguments);
        $definition->setPublic(false);

        return $definition;
    }
}
