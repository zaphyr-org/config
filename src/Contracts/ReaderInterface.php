<?php

declare(strict_types=1);

namespace Zaphyr\Config\Contracts;

use Zaphyr\Config\Exceptions\ReaderException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ReaderInterface
{
    /**
     * @param string $file
     *
     * @return array<string, mixed>
     *
     * @throws ReaderException
     */
    public function read(string $file): array;
}
