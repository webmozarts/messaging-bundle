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

namespace Cwd\MessagingBundle;

use Cwd\MessagingBundle\Annotation\MetadataResolver\AsyncMetadataResolver;
use Cwd\MessagingBundle\Annotation\MetadataResolver\ChannelMetadataResolver;
use Cwd\MessagingBundle\Annotation\MetadataResolver\DelegatingMetadataResolver;
use Cwd\MessagingBundle\Annotation\MetadataResolver\QualifierMetadataResolver;
use Cwd\MessagingBundle\Annotation\MetadataResolver\RetryMetadataResolver;
use Cwd\MessagingBundle\Annotation\MetadataResolver\RoutingKeyMetadataResolver;
use Cwd\MessagingBundle\Annotation\MetadataResolver\RunAsMetadataResolver;
use Cwd\MessagingBundle\Annotation\MetadataResolver\ThrottleMetadataResolver;
use Cwd\MessagingBundle\Channel\Factory\AnnotationBasedChannelFactory;
use Cwd\MessagingBundle\DependencyInjection\Compiler\AddHandlerDescriptorsPass;
use Cwd\MessagingBundle\DependencyInjection\Compiler\InjectTaggedServicesPass;
use Cwd\MessagingBundle\DependencyInjection\Compiler\RegisterQualifiersPass;
use Cwd\MessagingBundle\DependencyInjection\CwdMessagingExtension;
use Cwd\MessagingBundle\MetadataResolver\AnnotationBasedMetadataResolver;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CwdMessagingBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new CwdMessagingExtension('cwd_messaging');
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
            'cwd_messaging.command_bus',
            'cwd_messaging.command_bus_middleware',
            0
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'cwd_messaging.event_bus',
            'cwd_messaging.event_bus_middleware',
            0
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'cwd_messaging.event_bus.aggregates_recorded_messages',
            'cwd_messaging.event_recorder',
            0
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'cwd_messaging.command_bus.handler_invoker',
            'cwd_messaging.command_handler_invoker_middleware',
            0
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'cwd_messaging.event_bus.handler_invoker',
            'cwd_messaging.event_handler_invoker_middleware',
            0
        ));

        $container->addCompilerPass(new AddHandlerDescriptorsPass(
            $metadataResolver,
            $channelFactory,
            'cwd_messaging.command_bus.predefined_handler_descriptor_resolver',
            'cwd_messaging.channel_registry',
            'cwd_messaging.command_handler',
            'handle'
        ));

        $container->addCompilerPass(new AddHandlerDescriptorsPass(
            $metadataResolver,
            $channelFactory,
            'cwd_messaging.event_bus.predefined_handler_descriptor_resolver',
            'cwd_messaging.channel_registry',
            'cwd_messaging.event_handler',
            'when'
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'cwd_messaging.asynchronous.command_bus',
            'cwd_messaging.asynchronous_command_bus_middleware',
            0
        ));

        $container->addCompilerPass(new InjectTaggedServicesPass(
            'cwd_messaging.asynchronous.event_bus',
            'cwd_messaging.asynchronous_event_bus_middleware',
            0
        ));

        $container->addCompilerPass(new RegisterQualifiersPass(
            'cwd_messaging.asynchronous.gateway',
            'registerQualifier',
            'cwd_messaging.asynchronous_message_bus',
            'qualifier'
        ));
    }
}
