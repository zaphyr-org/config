<?php

declare(strict_types=1);

namespace Zaphyr\Config\Readers;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Zaphyr\Config\Contracts\ReaderInterface;
use Zaphyr\Config\Exceptions\ReaderException;

/**
 * Class YamlReader
 *
 * @package Zaphyr\Config\Readers
 * @author  merloxx <hello@merloxx.it>
 */
class YamlReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read(string $file): array
    {
        try {
            return Yaml::parseFile($file);
        } catch (ParseException $exception) {
            throw new ReaderException($exception->getMessage());
        }
    }
}
