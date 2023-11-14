<?php

declare(strict_types=1);

namespace Zaphyr\ConfigTests\Unit\Replacers;

use PHPUnit\Framework\TestCase;
use Zaphyr\Config\Exceptions\ReplacerException;
use Zaphyr\Config\Replacers\EnvReplacer;

class EnvReplacerTest extends TestCase
{
    /**
     * @var EnvReplacer
     */
    protected EnvReplacer $envReplacer;

    protected function setUp(): void
    {
        $this->envReplacer = new EnvReplacer();
    }

    protected function tearDown(): void
    {
        unset($this->envReplacer);
    }

    /**
     * ------------------------------------------
     * REPLACE
     * ------------------------------------------
     */

    public function testReplace(): void
    {
        $_ENV['value'] = 'foo';

        self::assertSame('foo', $this->envReplacer->replace('value'));
    }

    public function testReplaceWithQuotes(): void
    {
        $_ENV['value'] = 'foo';

        self::assertSame('foo', $this->envReplacer->replace('value'));
    }

    public function testReplaceTrue(): void
    {
        $_ENV['value'] = 'true';

        self::assertTrue($this->envReplacer->replace('value'));

        $_ENV['value'] = '(true)';

        self::assertTrue($this->envReplacer->replace('value'));
    }

    public function testReplaceFalse(): void
    {
        $_ENV['value'] = 'false';

        self::assertFalse($this->envReplacer->replace('value'));

        $_ENV['value'] = '(false)';

        self::assertFalse($this->envReplacer->replace('value'));
    }

    public function testReplaceEmpty(): void
    {
        $_ENV['value'] = '';

        self::assertSame('', $this->envReplacer->replace('value'));

        $_ENV['value'] = 'empty';

        self::assertSame('', $this->envReplacer->replace('value'));

        $_ENV['value'] = 'empty';

        self::assertSame('', $this->envReplacer->replace('value'));
    }

    public function testReplaceNull(): void
    {
        $_ENV['value'] = 'null';

        self::assertNull($this->envReplacer->replace('value'));

        $_ENV['value'] = '(null)';

        self::assertNull($this->envReplacer->replace('value'));
    }

    public function testReplaceRemovesQuotes(): void
    {
        $_ENV['value'] = '"foo"';

        self::assertEquals('foo', $this->envReplacer->replace('value'));
    }

    public function testReplaceThrowsExceptionIfEnvironmentVariableDoesNotExist(): void
    {
        $this->expectException(ReplacerException::class);

        $this->envReplacer->replace('NOPE');
    }
}
