<?php

declare(strict_types=1);

namespace Zaphyr\Config\Readers;

use ParseError;
use Zaphyr\Config\Contracts\ReaderInterface;
use Zaphyr\Config\Exceptions\ReaderException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ArrayReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read(string $file): array
    {
        try {
            return require $file;
        } catch (ParseError $exception) {
            throw new ReaderException($exception->getMessage());
        }
    }
}
