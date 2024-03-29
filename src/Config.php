<?php

declare(strict_types=1);

namespace Zaphyr\Config;

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
     * @var array<string, string>
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
     * @var array<string, string>
     */
    protected array $replacers = [
        'env' => EnvReplacer::class,
    ];

    /**
     * @var ReplacerInterface[]
     */
    protected array $cachedReplacers = [];

    /**
     * @param array<int|string, mixed>|null    $items
     * @param array<string, class-string>|null $readers
     * @param array<string, class-string>|null $replacers
     *
     * @throws ConfigException
     * @throws ReaderException
     */
    public function __construct(array|null $items = null, array|null $readers = null, array|null $replacers = null)
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
     * @throws ConfigException
     * @throws ReaderException
     */
    protected function loadFromDirectory(string $path): void
    {
        if (!is_readable($path)) {
            throw new ConfigException('The path "' . $path . '" is not readable');
        }

        $files = glob($path . '/*');

        if (!is_array($files)) {
            throw new ConfigException('The path "' . $path . '" is not readable');
        }

        foreach ($files as $file) {
            $namespace = pathinfo($file, PATHINFO_FILENAME);

            $this->loadFromFile($namespace, $file);
        }
    }

    /**
     * @param string $namespace
     * @param string $file
     *
     * @throws ConfigException
     * @throws ReaderException
     */
    protected function loadFromFile(string $namespace, string $file): void
    {
        if (!is_readable($file)) {
            throw new ConfigException('The file "' . $file . '" is not readable');
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (!array_key_exists($extension, $this->readers)) {
            throw new ConfigException('The file extension "' . $extension . '" has no valid reader');
        }

        $items = $this->getReaderInstance($extension)->read($file);

        array_walk_recursive($items, [$this, 'makeReplacements']);

        $this->items = array_merge($this->items, [$namespace => $items]);
    }

    /**
     * @param string $reader
     *
     * @return ReaderInterface
     */
    protected function getReaderInstance(string $reader): ReaderInterface
    {
        if (!isset($this->cachedReaders[$reader])) {
            $this->cachedReaders[$reader] = $this->container !== null
                ? $this->container->get($this->readers[$reader])
                : new $this->readers[$reader]();
        }

        return $this->cachedReaders[$reader];
    }

    /**
     * @param mixed $item
     *
     * @throws ConfigException
     * @throws ReplacerException
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
     * @throws ConfigException
     * @throws ReplacerException
     * @return mixed
     */
    protected function getReplaceValue(string $search, string $item): mixed
    {
        [$name, $value] = explode(':', $search, 2);

        if (!array_key_exists($name, $this->replacers)) {
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
        if (!isset($this->cachedReplacers[$replacer])) {
            $this->cachedReplacers[$replacer] = $this->container !== null
                ? $this->container->get($this->replacers[$replacer])
                : new $this->replacers[$replacer]();
        }

        return $this->cachedReplacers[$replacer];
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
        $item = $this->items;

        foreach (explode('.', $id) as $segment) {
            if (isset($item[$segment])) {
                $item = $item[$segment];
                continue;
            }

            $item = null;
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
