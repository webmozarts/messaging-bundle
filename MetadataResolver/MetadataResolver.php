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

namespace Cwd\MessagingBundle\MetadataResolver;

use ReflectionClass;
use ReflectionMethod;

interface MetadataResolver
{
    public function resolveClassMetadata(ReflectionClass $class): array;

    public function resolveMethodMetadata(ReflectionMethod $method): array;

    public function addClassListener(callable $listener): void;

    public function removeClassListener(callable $listener): void;
}
