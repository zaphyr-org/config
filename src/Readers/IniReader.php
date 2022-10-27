<?php

declare(strict_types=1);

namespace Zaphyr\Config\Readers;

use Exception;
use Zaphyr\Config\Contracts\ReaderInterface;
use Zaphyr\Config\Exceptions\ReaderException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class IniReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read(string $file): array
    {
        try {
            $contents = parse_ini_file($file, true);

            if (!is_array($contents)) {
                throw new ReaderException('Could not read file "' . $file . '"');
            }

            return $contents;
        } catch (Exception $exception) {
            throw new ReaderException($exception->getMessage());
        }
    }
}
