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

namespace Webmozarts\MessagingBundle\Routing;

use Webmozarts\MessagingBundle\Annotation\MetadataResolver\RoutingKeyMetadataResolver;
use Webmozarts\MessagingBundle\HandlerDescriptor\HandlerDescriptor;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionLanguageBasedRoutingKeyResolver implements RoutingKeyResolver
{
    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage = null)
    {
        $this->expressionLanguage = $expressionLanguage ?: new ExpressionLanguage();
        $this->expressionLanguage->addFunction(ExpressionFunction::fromPhp('substr'));
    }

    public function resolveRoutingKey($message, HandlerDescriptor $handlerDescriptor): ?string
    {
        $expression = $handlerDescriptor->getMetadata()[RoutingKeyMetadataResolver::EXPRESSION] ?? null;

        if (null === $expression) {
            return null;
        }

        return $this->expressionLanguage->evaluate($expression, [
            'message' => $message,
        ]);
    }
}
