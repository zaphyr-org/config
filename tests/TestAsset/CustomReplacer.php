<?php

declare(strict_types=1);

namespace Zaphyr\ConfigTests\TestAsset;

use Zaphyr\Config\Contracts\ReplacerInterface;

class CustomReplacer implements ReplacerInterface
{
    public function replace(string $value): string
    {
        return $value;
    }
}
