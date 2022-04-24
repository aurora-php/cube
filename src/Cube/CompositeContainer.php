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

use Aurora\Cube\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class CompositeContainer implements ContainerInterface
{
    /**
     * @param ContainerInterface[] $containers
     */
    public function __construct(
        private array $containers = []
    ) {}

    /**
     * @param ContainerInterface $container
     *
     * @return CompositeContainer
     */
    public function add(ContainerInterface $container): CompositeContainer
    {
        $this->containers[] = $container;

        return $this;
    }

    /**
     * @param string $id
     *
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    public function get(string $id): mixed
    {
        foreach ($this->containers as $container) {
            if ($container->has($id)) {
                return $container->get($id);
            }
        }

        throw new NotFoundException($id);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        foreach ($this->containers as $container) {
            if ($container->has($id)) {
                return true;
            }
        }

        return false;
    }
}
