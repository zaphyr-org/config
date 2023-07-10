<?php

declare(strict_types=1);

namespace Zaphyr\ConfigTests\Readers;

use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Exceptions\ReaderException;
use Zaphyr\Config\Readers\NeonReader;

class NeonReaderTest extends TestCase
{
    /**
     * @var string
     */
    protected string $tempFile;

    public function setUp(): void
    {
        file_put_contents($this->tempFile = __DIR__ . '/temp.neon', 'foo: bar');
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
        self::assertEquals(['foo' => 'bar'], (new NeonReader())->read(__DIR__ . '/temp.neon'));
    }

    public function testReadThrowsExceptionWhenFileIsBroken(): void
    {
        $this->expectException(ReaderException::class);

        file_put_contents($this->tempFile, '[array');

        (new NeonReader())->read($this->tempFile);
    }
}
