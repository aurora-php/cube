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

use Aurora\Cube\Exception\InitializationException;
use Aurora\Cube\Exception\NonUniqueEntryException;
use Aurora\Cube\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use Throwable;

final class LazyInitializingContainer implements ContainerInterface
{
    /**
     * @param array<string, callable> $initializers
     */
    public function __construct(
        private array $initializers = []
    ) {}

    /**
     * @param string   $id
     * @param callable $initializer
     *
     * @throws NonUniqueEntryException
     *
     * @return LazyInitializingContainer
     */
    public function add(string $id, callable $initializer): LazyInitializingContainer
    {
        if ($this->has($id)) {
            throw new NonUniqueEntryException($id);
        }
        $this->initializers[$id] = $initializer;

        return $this;
    }

    /**
     * @param string $id
     *
     * @throws NotFoundException
     * @throws InitializationException
     *
     * @return mixed
     */
    public function get(string $id): mixed
    {
        $initializer = $this->initializers[$id] ?? throw new NotFoundException($id);

        try {
            return $initializer();
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
        return \array_key_exists($id, $this->initializers);
    }
}
