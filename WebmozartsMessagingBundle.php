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

namespace Webmozarts\MessagingBundle;

use Webmozarts\MessagingBundle\Annotation\MetadataResolver\AsyncMetadataResolver;
use Webmozarts\MessagingBundle\Annotation\MetadataResolver\ChannelMetadataResolver;
use Webmozarts\MessagingBundle\Annotation\MetadataResolver\DelegatingMetadataResolver;
use Webmozarts\MessagingBundle\Annotation\MetadataResolver\QualifierMetadataResolver;
use Webmozarts\MessagingBundle\Annotation\MetadataResolver\RetryMetadataResolver;
use Webmozarts\MessagingBundle\Annotation\MetadataResolver\RoutingKeyMetadataResolver;
use Webmozarts\MessagingBundle\Annotation\MetadataResolver\RunAsMetadataResolver;
use Webmozarts\MessagingBundle\Annotation\MetadataResolver\ThrottleMetadataResolver;
use Webmozarts\MessagingBundle\Channel\Factory\AnnotationBasedChannelFactory;
use Webmozarts\MessagingBundle\DependencyInjection\Compiler\AddHandlerDescriptorsPass;
use Webmozarts\MessagingBundle\DependencyInjection\Compiler\InjectTaggedServicesPass;
use Webmozarts\MessagingBundle\DependencyInjection\Compiler\RegisterQualifiersPass;
use Webmozarts\MessagingBundle\DependencyInjection\WebmozartsMessagingExtension;
use Webmozarts\MessagingBundle\MetadataResolver\AnnotationBasedMetadataResolver;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WebmozartsMessagingBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new WebmozartsMessagingExtension('webmozarts_messaging');
    }

    public function build(ContainerBuilder $container)
    {
        $annotationReader = new AnnotationReader();
        $annotationMetadataResolver = new DelegatingMetadataResolver([
            new AsyncMetadataResolver(),
            new ChannelMetadataResolver(),
            new QualifierMetadataResolver(),
            new RetryMetadataResolver(),
            new RoutingKeyMetadataResolver(),
            new RunAsMetadataResolver(),
            new ThrottleMetadataResolver(),
        ]);
        $metadataResolver = new AnnotationBasedMetadataResolver(
            $annotationReader,
            $annotationMetadataResolver
        );
        $channelFactory = new AnnotationBasedChannelFactory($annotationReader);

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'webmozarts_messaging.command_bus',
            'webmozarts_messaging.command_bus_middleware',
            0
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'webmozarts_messaging.event_bus',
            'webmozarts_messaging.event_bus_middleware',
            0
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'webmozarts_messaging.event_bus.aggregates_recorded_messages',
            'webmozarts_messaging.event_recorder',
            0
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'webmozarts_messaging.command_bus.handler_invoker',
            'webmozarts_messaging.command_handler_invoker_middleware',
            0
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'webmozarts_messaging.event_bus.handler_invoker',
            'webmozarts_messaging.event_handler_invoker_middleware',
            0
        ));

        $container->addCompilerPass(new AddHandlerDescriptorsPass(
            $metadataResolver,
            $channelFactory,
            'webmozarts_messaging.command_bus.predefined_handler_descriptor_resolver',
            'webmozarts_messaging.channel_registry',
            'webmozarts_messaging.command_handler',
            'handle'
        ));

        $container->addCompilerPass(new AddHandlerDescriptorsPass(
            $metadataResolver,
            $channelFactory,
            'webmozarts_messaging.event_bus.predefined_handler_descriptor_resolver',
            'webmozarts_messaging.channel_registry',
            'webmozarts_messaging.event_handler',
            'when'
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'webmozarts_messaging.asynchronous.command_bus',
            'webmozarts_messaging.asynchronous_command_bus_middleware',
            0
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'webmozarts_messaging.asynchronous.event_bus',
            'webmozarts_messaging.asynchronous_event_bus_middleware',
            0
        ));

        $container->addCompilerPass(new RegisterQualifiersPass(
            'webmozarts_messaging.asynchronous.gateway',
            'registerQualifier',
            'webmozarts_messaging.asynchronous_message_bus',
            'qualifier'
        ));
    }
}
