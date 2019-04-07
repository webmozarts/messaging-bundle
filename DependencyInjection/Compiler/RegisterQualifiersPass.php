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
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterQualifiersPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var string
     */
    private $tagName;

    /**
     * @var string
     */
    private $tagKey;

    public function __construct(string $serviceName, string $methodName, string $tagName, string $tagKey)
    {
        $this->serviceName = $serviceName;
        $this->methodName = $methodName;
        $this->tagName = $tagName;
        $this->tagKey = $tagKey;
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

        foreach ($container->findTaggedServiceIds($this->tagName) as $serviceName => $tagPayloads) {
            foreach ($tagPayloads as $tagPayload) {
                $qualifier = $tagPayload[$this->tagKey];

                $definition->addMethodCall($this->methodName, [$qualifier, $serviceName]);
            }
        }
    }
}
