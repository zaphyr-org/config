<?php

declare(strict_types=1);

namespace Zaphyr\ConfigTests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Zaphyr\Config\Config;
use Zaphyr\Config\Exceptions\ConfigException;
use Zaphyr\Config\Readers\ArrayReader;
use Zaphyr\ConfigTests\TestAsset\CustomExtensionReader;
use Zaphyr\ConfigTests\TestAsset\CustomReplacer;

class ConfigTest extends TestCase
{
    /**
     * @var string
     */
    protected $tempFile;

    /**
     * @var string
     */
    protected $tempDir;

    public function setUp(): void
    {
        file_put_contents(
            $this->tempFile = __DIR__.'/TestAsset/temp.php',
            '<?php return ["foo" => ["bar" => "baz"]];'
        );
        mkdir($this->tempDir = __DIR__.'/TestAsset/temp');
    }

    public function tearDown(): void
    {
        unlink($this->tempFile);
        rmdir($this->tempDir);
    }

    /**
     * @param array<string, mixed> $expected
     * @param string               $actualAttributeName
     * @param object               $actualClassOrObject
     */
    protected static function assertPropertyEquals(
        array $expected,
        string $actualAttributeName,
        object $actualClassOrObject
    ): void {
        $reflection = new ReflectionClass($actualClassOrObject);
        $property = $reflection->getProperty($actualAttributeName);
        $property->setAccessible(true);

        $value = $property->getValue($actualClassOrObject);

        self::assertEquals($expected, $value);
    }

    /**
     * ------------------------------------------
     * CONSTRUCTOR
     * ------------------------------------------.
     */
    public function testLoadFilesFromConstructor(): void
    {
        $config = new Config(
            [
                'config' => $this->tempFile,
            ]
        );

        self::assertEquals(['config' => ['foo' => ['bar' => 'baz']]], $config->toArray());
    }

    public function testLoadDirectoryFromConstructor(): void
    {
        $config = new Config([__DIR__.'/TestAsset/config']);
        $data = $config->toArray();

        self::assertArrayHasKey('array', $data);
        self::assertArrayHasKey('ini', $data);
        self::assertArrayHasKey('json', $data);
        self::assertArrayHasKey('xml', $data);
        self::assertArrayHasKey('yaml', $data);
        self::assertArrayHasKey('yml', $data);
    }

    public function testAddReadersInConstructor(): void
    {
        $config = new Config(null, ['custom' => CustomExtensionReader::class]);
        $readers = $config->getReaders();

        self::assertEquals(CustomExtensionReader::class, $readers['custom']);
    }

    public function testAddReplacersInConstructor(): void
    {
        $config = new Config(null, null, ['custom' => CustomReplacer::class]);
        $replacers = $config->getReplacers();

        self::assertEquals(CustomReplacer::class, $replacers['custom']);
    }

    /**
     * ------------------------------------------
     * LOAD
     * ------------------------------------------.
     */
    public function testLoadWithFiles(): void
    {
        $config = new Config();
        $config->load(['config' => $this->tempFile]);

        self::assertEquals(['config' => ['foo' => ['bar' => 'baz']]], $config->toArray());
    }

    public function testLoadWithDirectory(): void
    {
        $config = new Config();
        $config->load([__DIR__.'/TestAsset/config']);
        $data = $config->toArray();

        self::assertArrayHasKey('array', $data);
        self::assertArrayHasKey('ini', $data);
        self::assertArrayHasKey('json', $data);
        self::assertArrayHasKey('xml', $data);
        self::assertArrayHasKey('yaml', $data);
        self::assertArrayHasKey('yml', $data);
    }

    public function testLoadThrowsExceptionWhenNamespaceIsAlreadyInUse(): void
    {
        $this->expectException(ConfigException::class);

        $config = new Config();

        $config->load(
            [
                'foo' => __DIR__.'/TestAsset/config/array.php',
                'bar' => __DIR__.'/TestAsset/config/json.json',
            ]
        );

        $config->load(
            [
                'foo' => __DIR__.'/TestAsset/config/yml.yml',
            ]
        );
    }

    public function testLoadThrowsExceptionWhenConfigIsNotAFileOrDirectoryString(): void
    {
        $this->expectException(ConfigException::class);

        $config = new Config();
        $config->load(
            [
                'foo' => [
                    'bar' => 'baz',
                ],
            ]
        );
    }

    public function testLoadThrowsExceptionWhenFileIsNotReadable(): void
    {
        $this->expectException(ConfigException::class);

        chmod($this->tempFile, 0000);

        $config = new Config();
        $config->load(['config' => $this->tempFile]);
    }

    public function testLoadThrowsExceptionWhenDirectoryIsNotReadable(): void
    {
        $this->expectException(ConfigException::class);

        chmod($this->tempDir, 0000);

        $config = new Config();
        $config->load([$this->tempDir]);
    }

    public function testLoadThrowsExceptionWhenFileExtensionInsInvalid(): void
    {
        $this->expectException(ConfigException::class);

        $config = new Config();
        $config->load(['invalid' => __DIR__.'/TestAsset/invalid/unsupported.extension']);
    }

    public function testLoadThrowsExceptionWhenFileExtensionInDirectoryIsInvalid(): void
    {
        $this->expectException(ConfigException::class);

        $config = new Config();
        $config->load([__DIR__.'/TestAsset/invalid']);
    }

    public function testLoadReplacesPlaceholders(): void
    {
        file_put_contents(
            $this->tempFile = __DIR__.'/TestAsset/placeholder.php',
            '<?php return ["driver" => "%env:DB_DRIVER%"];'
        );

        $_ENV['DB_DRIVER'] = 'mysql';

        $config = new Config(['db' => $this->tempFile]);

        self::assertEquals(['db' => ['driver' => 'mysql']], $config->toArray());
    }

    public function testLoadReplacesOnlyReplacePortion(): void
    {
        file_put_contents(
            $this->tempFile = __DIR__.'/TestAsset/placeholder.php',
            '<?php return ["name" => "SITE NAME: %env:SITE_NAME% (online)"];'
        );

        $_ENV['SITE_NAME'] = 'merloxx';

        $config = new Config(['config' => $this->tempFile]);

        self::assertEquals(['config' => ['name' => 'SITE NAME: merloxx (online)']], $config->toArray());
    }

    public function testLoadThrowsExceptionIfConfigFileOrDirectoryDoesNotExist(): void
    {
        $this->expectException(ConfigException::class);

        (new Config())->load(['nope']);
    }

    public function testLoadCachesReaderInstances(): void
    {
        $config = new Config();

        self::assertPropertyEquals([], 'cachedReaders', $config);

        $config->load(['config' => $this->tempFile]);

        self::assertPropertyEquals(['php' => new ArrayReader()], 'cachedReaders', $config);
    }

    public function testLoadCachesReplacerInstances(): void
    {
        file_put_contents(
            $this->tempFile = __DIR__.'/TestAsset/replacer.php',
            '<?php return ["foo" => "The value %this:string% is replaced"];'
        );

        $config = new Config();

        self::assertPropertyEquals([], 'cachedReplacers', $config);

        $config->addReplacer('this', CustomReplacer::class);
        $config->load(['config' => $this->tempFile]);

        self::assertPropertyEquals(['this' => new CustomReplacer()], 'cachedReplacers', $config);
    }

    /**
     * ------------------------------------------
     * HAS
     * ------------------------------------------.
     */
    public function testHasReturnsTrueWhenConfigItemExists(): void
    {
        $config = new Config(['config' => $this->tempFile]);

        self::assertTrue($config->has('config.foo.bar'));
    }

    public function testHasReturnsTrueWhenConfigItemDoesNotExists(): void
    {
        $config = new Config(['config' => $this->tempFile]);

        self::assertFalse($config->has('nope'));
    }

    /**
     * ------------------------------------------
     * GET
     * ------------------------------------------.
     */
    public function testGetReturnsConfigItem(): void
    {
        $config = new Config(['config' => $this->tempFile]);

        self::assertEquals(['bar' => 'baz'], $config->get('config.foo'));
        self::assertEquals('baz', $config->get('config.foo.bar'));
    }

    public function testGetReturnsNullByDefaultWhenConfigItemDoesNotExists(): void
    {
        $config = new Config(['config' => $this->tempFile]);

        self::assertNull($config->get('nope'));
    }

    public function testGetReturnsCustomValueWhenConfigItemDoesNotExists(): void
    {
        $config = new Config(['config' => $this->tempFile]);

        self::assertEquals('Not available', $config->get('nope', 'Not available'));
    }

    public function testGetDoesNotCacheDefaultValue(): void
    {
        $config = new Config(['config' => $this->tempFile]);

        self::assertEquals('Not available', $config->get('nope', 'Not available'));
        self::assertNull($config->get('nope'));
    }

    public function testGetLoadsFromCache(): void
    {
        $config = new Config(['config' => $this->tempFile]);

        self::assertEquals(['bar' => 'baz'], $config->get('config.foo'));
        self::assertPropertyEquals($cache = ['config.foo' => ['bar' => 'baz']], 'cachedItems', $config);

        self::assertEquals('baz', $config->get('config.foo.bar'));
        self::assertPropertyEquals(array_merge($cache, ['config.foo.bar' => 'baz']), 'cachedItems', $config);

        // This line is just there to get the 100% test coverage and to satisfy my OCD
        self::assertEquals('baz', $config->get('config.foo.bar'));
    }

    /**
     * ------------------------------------------
     * SET|GET ITEMS
     * ------------------------------------------.
     */
    public function testSetAndGetItems(): void
    {
        $config = new Config();

        self::assertEquals([], $config->getItems());

        $config->setItems($items = ['foo' => 'bar']);

        self::assertEquals($items, $config->getItems());
    }

    /**
     * ------------------------------------------
     * ADD READER
     * ------------------------------------------.
     */
    public function testAddReader(): void
    {
        $config = new Config();
        $config->addReader('extension', CustomExtensionReader::class);
        $config->load(['config' => __DIR__.'/TestAsset/invalid/unsupported.extension']);

        self::assertEquals(['config' => ['foo' => 'bar']], $config->toArray());
    }

    public function testReaderThrowsExeptionWhenReaderNameIsAlreadyInUse(): void
    {
        $this->expectException(ConfigException::class);

        $config = new Config();
        $config->addReader('php', CustomExtensionReader::class);
    }

    public function testExistingReaderCanBeOverwrittenWithForceFlag(): void
    {
        $config = new Config();
        $config->addReader('php', CustomExtensionReader::class, true);

        $readers = $config->getReaders();

        self::assertEquals(CustomExtensionReader::class, $readers['php']);
    }

    /**
     * ------------------------------------------
     * ADD REPLACER
     * ------------------------------------------.
     */
    public function testAddReplacer(): void
    {
        file_put_contents(
            $this->tempFile = __DIR__.'/TestAsset/replacer.php',
            '<?php return ["foo" => "The value %this:string% is replaced"];'
        );

        $config = new Config();
        $config->addReplacer('this', CustomReplacer::class);
        $config->load(['config' => $this->tempFile]);

        self::assertEquals('The value string is replaced', $config->get('config.foo'));
    }

    public function testReplacerThrowsExceptionWhenReplacerNameIsAlreadyInUse(): void
    {
        $this->expectException(ConfigException::class);

        $config = new Config();
        $config->addReplacer('env', CustomReplacer::class);
    }

    public function testExistingReplacerCanBeOverwrittenWithForceFlag(): void
    {
        $config = new Config();
        $config->addReplacer('env', CustomReplacer::class, true);

        $replacers = $config->getReplacers();

        self::assertEquals(CustomReplacer::class, $replacers['env']);
    }

    public function testThrowsExceptionWhenReplacerIsNotAvailable(): void
    {
        $this->expectException(ConfigException::class);

        file_put_contents(
            $tempFile = __DIR__.'/TestAsset/replacer.php',
            '<?php return ["foo" => "The value %nope:string% is replaced"];'
        );

        try {
            $config = new Config();
            $config->load(['config' => $tempFile]);
        } catch (ConfigException $e) {
            unlink($tempFile);

            throw $e;
        }
    }
}
