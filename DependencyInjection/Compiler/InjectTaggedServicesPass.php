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

namespace Webmozarts\MessagingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InjectTaggedServicesPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var string
     */
    private $tagName;

    /**
     * @var int
     */
    private $argumentPosition;

    public function __construct(string $serviceName, string $tagName, int $argumentPosition)
    {
        $this->serviceName = $serviceName;
        $this->tagName = $tagName;
        $this->argumentPosition = $argumentPosition;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->serviceName)) {
            return;
        }

        $definition = $container->getDefinition($this->serviceName);

        $definition->setArgument(
            $this->argumentPosition,
            $this->findAndSortTaggedServices($this->tagName, $container)
        );
    }
}
