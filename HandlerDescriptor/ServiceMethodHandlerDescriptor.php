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

namespace Cwd\MessagingBundle\HandlerDescriptor;

class ServiceMethodHandlerDescriptor implements HandlerDescriptor
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
     * @var array
     */
    private $metadata;

    public function __construct(string $serviceName, string $methodName, array $metadata)
    {
        $this->serviceName = $serviceName;
        $this->methodName = $methodName;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function withMetadata(array $metadata): HandlerDescriptor
    {
        return new static($this->serviceName, $this->methodName, array_filter(array_replace($this->metadata, $metadata)));
    }
}
