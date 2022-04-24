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

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class CachingContainer implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $cache = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        private readonly ContainerInterface $container
    ) {}

    /**
     * @param string $id
     *
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    public function get(string $id): mixed
    {
        if (\array_key_exists($id, $this->cache)) {
            return $this->cache[$id];
        }
        $entry = $this->container->get($id);
        $this->cache[$id] = $entry;

        return $entry;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return \array_key_exists($id, $this->cache) || $this->container->has($id);
    }
}
