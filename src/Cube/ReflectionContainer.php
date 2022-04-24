<?php

declare(strict_types=1);

/*
 * This file is part of the Aurora Project.
 *
 * (c) Tentifly <info@tentifly.com>
 *
 * This file is subject to the MIT license.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Aurora\Cube;

use Aurora\Cube\Exception\ContainerException;
use Aurora\Cube\Exception\InitializationException;
use Aurora\Cube\Exception\NotFoundException;
use Aurora\Reflection\ReflectionClass;
use Aurora\Reflection\ReflectionUtils;
use Psr\Container\ContainerInterface;
use ReflectionParameter;
use Throwable;

final class ReflectionContainer implements ContainerInterface
{
    /**
     * @param string $id
     *
     * @throws ContainerException
     *
     * @return object
     */
    public function get(string $id): object
    {
        if (!class_exists($id)) {
            throw new NotFoundException($id);
        }

        try {
            $class = new ReflectionClass($id);
            if (!$class->isInstantiable()) {
                throw new ContainerException("'{$id}' is not instantiable");
            }
            $arguments = array_map(fn($parameter) => $this->getParameter($parameter), $class->getInstanceArgs());

            return $class->newInstanceArgs($arguments);
        } catch (Throwable $e) {
            throw new InitializationException(message: $id, previous: $e);
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        if (!class_exists($id)) {
            return false;
        }

        $class = new ReflectionClass($id);
        if (!$class->isInstantiable()) {
            return false;
        }
        foreach ($class->getInstanceArgs() as $parameter) {
            if (!$this->hasParameter($parameter)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ReflectionParameter $parameter
     *
     * @throws ContainerException
     *
     * @return null|object
     */
    private function getParameter(ReflectionParameter $parameter): null|object
    {
        $reflection = $parameter->getType();
        if (null === $reflection) {
            return null;
        }

        foreach (ReflectionUtils::getTypes($reflection) as $type) {
            if ($this->has($type->getName())) {
                return $this->get($type->getName());
            }
        }

        throw new NotFoundException($parameter->getName());
    }

    /**
     * @param ReflectionParameter $parameter
     *
     * @return bool
     */
    private function hasParameter(ReflectionParameter $parameter): bool
    {
        $reflection = $parameter->getType();
        if (null === $reflection) {
            return true;
        }

        foreach (ReflectionUtils::getTypes($reflection) as $type) {
            if ($this->has($type->getName())) {
                return true;
            }
        }

        return false;
    }
}
