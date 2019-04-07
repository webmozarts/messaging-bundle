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

namespace Webmozarts\MessagingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class WebmozartsMessagingExtension extends ConfigurableExtension
{
    /**
     * @var string
     */
    private $alias;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->alias);
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('channel_registry.yml');
        $loader->load('command_bus.yml');
        $loader->load('event_bus.yml');

        if ($mergedConfig['async']['enabled']) {
            $loader->load('asynchronous_command_bus.yml');
            $loader->load('asynchronous_event_bus.yml');
            $loader->load('asynchronous_gateway.yml');
            $loader->load('asynchronous_serialization.yml');

            if ('rabbit_mq' === $mergedConfig['async']['transport']) {
                $loader->load('rabbit_mq.yml');

                $container->setAlias(
                    'webmozarts_messaging.rabbit_mq.connection',
                    'old_sound_rabbit_mq.connection.'.$mergedConfig['async']['rabbit_mq']['connection']
                );
            }
        } else {
            $container->getDefinition('webmozarts_messaging.command_bus.invokes_handlers_middleware')
                ->addTag('webmozarts_messaging.command_bus_middleware', ['priority' => -1000]);
            $container->getDefinition('webmozarts_messaging.event_bus.invokes_handlers_middleware')
                ->addTag('webmozarts_messaging.event_bus_middleware', ['priority' => -1000]);
        }

        if ($mergedConfig['doctrine_orm']['enabled']) {
            $loader->load('doctrine_orm.yml');

            $container->setParameter(
                'webmozarts_messaging.doctrine_orm.entity_manager',
                $mergedConfig['doctrine_orm']['entity_manager']
            );
        }

        if (null !== $mergedConfig['authentication']['user_provider']) {
            $loader->load('authentication.yml');

            $container->setParameter(
                'webmozarts_messaging.authentication.anonymous_user_id',
                $mergedConfig['authentication']['anonymous_user_id']
            );

            $container->setAlias(
                'webmozarts_messaging.authentication.user_provider',
                $mergedConfig['authentication']['user_provider']
            );
        }
    }
}
