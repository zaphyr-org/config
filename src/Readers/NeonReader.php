<?php

declare(strict_types=1);

namespace Zaphyr\Config\Readers;

use Exception;
use Nette\Neon\Neon;
use Zaphyr\Config\Contracts\ReaderInterface;
use Zaphyr\Config\Exceptions\ReaderException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class NeonReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read(string $file): array
    {
        try {
            return Neon::decodeFile($file);
        } catch (Exception $e) {
            throw new ReaderException($e->getMessage());
        }
    }
}
