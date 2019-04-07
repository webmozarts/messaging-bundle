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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private $alias;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root($this->alias);

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('authentication')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('user_provider')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('anonymous_user_id')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('async')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('transport')
                            ->values(['rabbit_mq'])
                            ->defaultValue('rabbit_mq')
                        ->end()
                        ->arrayNode('rabbit_mq')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('connection')
                                    ->defaultValue('default')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('logging')
                    ->canBeEnabled()
                ->end()
                ->arrayNode('doctrine_orm')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity_manager')
                            ->defaultValue('default')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
