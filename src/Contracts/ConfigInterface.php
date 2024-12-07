<?php

declare(strict_types=1);

namespace Zaphyr\Config\Contracts;

use Zaphyr\Config\Exceptions\ConfigException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ConfigInterface extends ContainerAwareInterface
{
    /**
     * @param array<int|string, mixed> $items
     *
     * @throws ConfigException
     */
    public function load(array $items): void;

    /**
     * @return array<string, mixed>
     * @deprecated Will be removed in v3.0. Use method "getItems" instead
     */
    public function toArray(): array;

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * @param string $id
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $id, mixed $default = null): mixed;

    /**
     * @param array<string, mixed> $items
     */
    public function setItems(array $items): void;

    /**
     * @return array<string, mixed>
     */
    public function getItems(): array;

    /**
     * @param string $name
     * @param string $reader
     * @param bool   $force
     *
     * @throws ConfigException
     * @return $this
     *
     */
    public function addReader(string $name, string $reader, bool $force = false): static;

    /**
     * @return array<string, string>
     */
    public function getReaders(): array;

    /**
     * @param string $name
     * @param string $replacer
     * @param bool   $force
     *
     * @throws ConfigException
     * @return $this
     */
    public function addReplacer(string $name, string $replacer, bool $force = false): static;

    /**
     * @return array<string, string>
     */
    public function getReplacers(): array;
}
