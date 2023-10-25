<?php

declare(strict_types=1);

namespace Zaphyr\ConfigTests\Readers;

use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Exceptions\ReaderException;
use Zaphyr\Config\Readers\IniReader;

class IniReaderTest extends TestCase
{
    /**
     * @var string
     */
    protected string $tempFile;

    protected function setUp(): void
    {
        file_put_contents($this->tempFile = __DIR__ . '/temp.php', 'foo=bar');
    }

    protected function tearDown(): void
    {
        unlink($this->tempFile);
    }

    /* -------------------------------------------------
     * READ
     * -------------------------------------------------
     */

    public function testRead(): void
    {
        self::assertEquals(['foo' => 'bar'], (new IniReader())->read($this->tempFile));
    }

    public function testReadThrowsExceptionWhenFileIsBroken(): void
    {
        $this->expectException(ReaderException::class);

        file_put_contents($this->tempFile, '[array');

        (new IniReader())->read($this->tempFile);
    }
}
