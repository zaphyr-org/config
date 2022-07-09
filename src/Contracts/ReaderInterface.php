<?php

declare(strict_types=1);

namespace Zaphyr\Config\Contracts;

use Zaphyr\Config\Exceptions\ReaderException;

/**
 * Interface LoaderInterface
 *
 * @package Zaphyr\Config\Contracts
 * @author  merloxx <hello@merloxx.it>
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
