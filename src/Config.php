<?php

declare(strict_types=1);

namespace Zaphyr\Config;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Zaphyr\Config\Contracts\ConfigInterface;
use Zaphyr\Config\Contracts\ReaderInterface;
use Zaphyr\Config\Contracts\ReplacerInterface;
use Zaphyr\Config\Exceptions\ConfigException;
use Zaphyr\Config\Exceptions\ReaderException;
use Zaphyr\Config\Exceptions\ReplacerException;
use Zaphyr\Config\Readers\ArrayReader;
use Zaphyr\Config\Readers\IniReader;
use Zaphyr\Config\Readers\JsonReader;
use Zaphyr\Config\Readers\NeonReader;
use Zaphyr\Config\Readers\XmlReader;
use Zaphyr\Config\Readers\YamlReader;
use Zaphyr\Config\Replacers\EnvReplacer;
use Zaphyr\Config\Traits\ContainerAwareTrait;
use Zaphyr\Utils\Arr;
use Zaphyr\Utils\File;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Config implements ConfigInterface
{
    use ContainerAwareTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $items = [];

    /**
     * @var array<string, mixed>
     */
    protected array $cachedItems = [];

    /**
     * @var array<string, class-string<ReaderInterface>>
     */
    protected array $readers = [
        'php' => ArrayReader::class,
        'ini' => IniReader::class,
        'json' => JsonReader::class,
        'xml' => XmlReader::class,
        'yml' => YamlReader::class,
        'yaml' => YamlReader::class,
        'neon' => NeonReader::class,
    ];

    /**
     * @var ReaderInterface[]
     */
    protected array $cachedReaders = [];

    /**
     * @var array<string, class-string<ReplacerInterface>>
     */
    protected array $replacers = [
        'env' => EnvReplacer::class,
    ];

    /**
     * @var ReplacerInterface[]
     */
    protected array $cachedReplacers = [];

    /**
     * @param array<int|string, mixed>|null                       $items
     * @param array<string, class-string<ReaderInterFace>>|null   $readers
     * @param array<string, class-string<ReplacerInterface>>|null $replacers
     *
     * @throws ConfigException|ReaderException
     */
    public function __construct(?array $items = null, ?array $readers = null, ?array $replacers = null)
    {
        if ($readers) {
            foreach ($readers as $name => $reader) {
                $this->addReader($name, $reader);
            }
        }

        if ($replacers) {
            foreach ($replacers as $name => $replacer) {
                $this->addReplacer($name, $replacer);
            }
        }

        if ($items) {
            $this->load($items);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReaderException
     */
    public function load(array $items): void
    {
        foreach ($items as $namespace => $item) {
            if (isset($this->items[$namespace])) {
                throw new ConfigException('The namespace "' . $namespace . '" is already in use');
            }

            if (!is_string($item)) {
                throw new ConfigException(
                    sprintf(
                        'The configuration item must be a file or directory string, "%s" given',
                        is_object($item) ? get_class($item) : gettype($item)
                    )
                );
            }

            if (!(is_file($item) || is_dir($item))) {
                throw new ConfigException('The configuration file or directory "' . $item . '" does not exist');
            }

            if (is_file($item)) {
                $this->loadFromFile((string)$namespace, $item);
            }

            if (is_dir($item)) {
                $this->loadFromDirectory($item);
            }
        }
    }

    /**
     * @param string $path
     *
     * @throws ConfigException|ReaderException
     */
    protected function loadFromDirectory(string $path): void
    {
        if (!$this->isReadable($path)) {
            throw new ConfigException('The path "' . $path . '" is not readable');
        }

        $files = File::allFiles($path);

        if (!is_array($files)) {
            throw new ConfigException('The path "' . $path . '" is not readable');
        }

        foreach ($files as $file) {
            $namespace = str_replace([$path, DIRECTORY_SEPARATOR], ['', '.'], $file->getPathname());
            $namespace = ltrim($namespace, '.');
            $namespace = pathinfo($namespace, PATHINFO_FILENAME);

            $this->loadFromFile($namespace, $file->getPathname());
        }
    }

    /**
     * @param string $namespace
     * @param string $file
     *
     * @throws ConfigException|ReaderException
     */
    protected function loadFromFile(string $namespace, string $file): void
    {
        if (!$this->isReadable($file)) {
            throw new ConfigException('The file "' . $file . '" is not readable');
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (!isset($this->readers[$extension])) {
            throw new ConfigException('The file extension "' . $extension . '" has no valid reader');
        }

        $items = $this->getReaderInstance($extension)->read($file);

        array_walk_recursive($items, [$this, 'makeReplacements']);

        $this->items = Arr::add($this->items, $namespace, $items);
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function isReadable(string $file): bool
    {
        return is_readable($file);
    }

    /**
     * @param string $reader
     *
     * @return ReaderInterface
     */
    protected function getReaderInstance(string $reader): ReaderInterface
    {
        return $this->cachedReaders[$reader] ??= $this->initializeReaderInstance($reader);
    }

    /**
     * @param string $reader
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     * @return ReaderInterface
     */
    protected function initializeReaderInstance(string $reader): ReaderInterface
    {
        return $this->container !== null
            ? $this->container->get($this->readers[$reader])
            : new $this->readers[$reader]();
    }

    /**
     * @param mixed $item
     *
     * @throws ConfigException|ReplacerException
     */
    protected function makeReplacements(mixed &$item): void
    {
        if (is_string($item) && str_contains($item, '%')) {
            $search = $this->getSearchString($item);
            $replace = $this->getReplaceValue($search, $item);

            if ($item === '%' . $search . '%') {
                $item = $replace;
            } else {
                $item = str_replace('%' . $search . '%', $replace, $item);
            }
        }
    }

    /**
     * @param string $item
     *
     * @return string
     */
    protected function getSearchString(string $item): string
    {
        $start = strpos($item, '%');
        $end = strpos($item, '%', $start + 1);

        return substr($item, $start + 1, $end - $start - 1);
    }

    /**
     * @param string $search
     * @param string $item
     *
     * @throws ConfigException|ReplacerException
     * @return mixed
     */
    protected function getReplaceValue(string $search, string $item): mixed
    {
        [$name, $value] = explode(':', $search, 2);

        if (!isset($this->replacers[$name])) {
            throw new ConfigException('The item "' . $item . '" has no valid replacer');
        }

        return $this->getReplacerInstance($name)->replace($value);
    }

    /**
     * @param string $replacer
     *
     * @return ReplacerInterface
     */
    protected function getReplacerInstance(string $replacer): ReplacerInterface
    {
        return $this->cachedReplacers[$replacer] ??= $this->initializeReplacerInstance($replacer);
    }

    /**
     * @param string $replacer
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     * @return ReplacerInterface
     */
    protected function initializeReplacerInstance(string $replacer): ReplacerInterface
    {
        return $this->container !== null
            ? $this->container->get($this->replacers[$replacer])
            : new $this->replacers[$replacer]();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return $this->extractFromConfig($id) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id, mixed $default = null): mixed
    {
        if (isset($this->cachedItems[$id])) {
            return $this->cachedItems[$id];
        }

        if (!$this->has($id)) {
            return $default;
        }

        return $this->cachedItems[$id] = $this->extractFromConfig($id);
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    protected function extractFromConfig(string $id): mixed
    {
        $segments = explode('.', $id);
        $item = &$this->items;

        foreach ($segments as $segment) {
            if (!isset($item[$segment])) {
                return null;
            }

            $item = &$item[$segment];
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items): void
    {
        $this->cachedItems = [];
        $this->items = $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function addReader(string $name, string $reader, bool $force = false): static
    {
        if (!$force && isset($this->readers[$name])) {
            throw new ConfigException('The reader with name "' . $name . '" is already in use');
        }

        $this->readers[$name] = $reader;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReaders(): array
    {
        return $this->readers;
    }

    /**
     * {@inheritdoc}
     */
    public function addReplacer(string $name, string $replacer, bool $force = false): static
    {
        if (!$force && isset($this->replacers[$name])) {
            throw new ConfigException('The replacer with name "' . $name . '" is already in use');
        }

        $this->replacers[$name] = $replacer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReplacers(): array
    {
        return $this->replacers;
    }
}
