<?php

declare(strict_types=1);

namespace Zaphyr\Config\Readers;

use LibXMLError;
use Zaphyr\Config\Contracts\ReaderInterface;
use Zaphyr\Config\Exceptions\ReaderException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class XmlReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read(string $file): array
    {
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
