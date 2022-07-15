<?php

declare(strict_types=1);

namespace Zaphyr\ConfigTests\Readers;

use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Exceptions\ReaderException;
use Zaphyr\Config\Readers\ArrayReader;

class ArrayReaderTest extends TestCase
{
    /**
     * @var string
     */
    protected $tempFile;

    public function setUp(): void
    {
        file_put_contents($this->tempFile = __DIR__.'/temp.php', '<?php return["foo" => "bar"];');
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
        self::assertEquals(['foo' => 'bar'], (new ArrayReader())->read($this->tempFile));
    }

    public function testReadThrowsExceptionWhenFileIsBroken(): void
    {
        $this->expectException(ReaderException::class);

        file_put_contents($this->tempFile, '<?php return ["foo;');

        (new ArrayReader())->read($this->tempFile);
    }
}
