<?php

declare(strict_types=1);

namespace Zaphyr\ConfigTests\Readers;

use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Exceptions\ReaderException;
use Zaphyr\Config\Readers\XmlReader;

class XmlReaderTest extends TestCase
{
    /**
     * @var string
     */
    protected string $tempFile;

    public function setUp(): void
    {
        file_put_contents(
            $this->tempFile = __DIR__ . '/temp.xml',
            '<?xml version="1.0" encoding="UTF-8"?><r><foo>bar</foo></r>'
        );
    }

    public function tearDown(): void
    {
        unlink($this->tempFile);
    }

    /* -------------------------------------------------
     * READ
     * -------------------------------------------------
     */

    public function testRead(): void
    {
        self::assertEquals(['foo' => 'bar'], (new XmlReader())->read($this->tempFile));
    }

    public function testReadThrowsExceptionWhenFileIsBroken(): void
    {
        $this->expectException(ReaderException::class);

        file_put_contents($this->tempFile, '<?xml version="1.0" encoding="UTF-8"?><r><foobar</foo></r>');

        (new XmlReader())->read($this->tempFile);
    }
}
