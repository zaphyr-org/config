<?php

declare(strict_types=1);

namespace Zaphyr\Config\Contracts;

use Zaphyr\Config\Exceptions\ReaderException;

/**
 * Interface LoaderInterface.
 *
 * @author  merloxx <merloxx@zaphyr.org>
 */
interface ReaderInterface
{
    /**
     * @param string $file
     *
     * @throws ReaderException
     *
     * @return array<string, mixed>
     */
    public function read(string $file): array;
}
