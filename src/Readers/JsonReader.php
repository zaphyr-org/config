<?php

declare(strict_types=1);

namespace Zaphyr\Config\Readers;

use Zaphyr\Config\Contracts\ReaderInterface;
use Zaphyr\Config\Exceptions\ReaderException;

/**
 * Class JsonReader
 *
 * @package Zaphyr\Config\Readers
 * @author  merloxx <merloxx@zaphyr.org>
 */
class JsonReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read(string $file): array
    {
        $contents = file_get_contents($file);

        if (!is_string($contents)) {
            throw new ReaderException('Could not read file "' . $file . '"');
        }

        $contents = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ReaderException(json_last_error_msg());
        }

        return $contents;
    }
}
