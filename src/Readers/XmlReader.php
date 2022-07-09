<?php

declare(strict_types=1);

namespace Zaphyr\Config\Readers;

use LibXMLError;
use Zaphyr\Config\Contracts\ReaderInterface;
use Zaphyr\Config\Exceptions\ReaderException;

/**
 * Class XmlReader
 *
 * @package Zaphyr\Config\Readers
 * @author  merloxx <hello@merloxx.it>
 */
class XmlReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read(string $file): array
    {
        // workaround for bug https://bugs.php.net/bug.php?id=62577
        // a patch is available since 2018-10-15
        libxml_disable_entity_loader(false);
        libxml_use_internal_errors(true);

        $contents = file_get_contents($file);

        if (!is_string($contents)) {
            throw new ReaderException('Could not read file "' . $file . '"');
        }

        $data = simplexml_load_string($contents);

        if ($data === false) {
            $error = libxml_get_last_error();

            if ($error instanceof LibXMLError) {
                throw new ReaderException($error->message);
            }

            throw new ReaderException('Could not read file "' . $file . '"');
        }

        $contents = json_encode($data);

        if (!is_string($contents)) {
            throw new ReaderException('Could not read file "' . $file . '"');
        }

        return json_decode($contents, true);
    }
}
