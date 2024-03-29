<?php

declare(strict_types=1);

namespace Zaphyr\Config\Replacers;

use Zaphyr\Config\Contracts\ReplacerInterface;
use Zaphyr\Config\Exceptions\ReplacerException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EnvReplacer implements ReplacerInterface
{
    /**
     * {@inheritdoc}
     */
    public function replace(string $value): mixed
    {
        $env = $_SERVER[$value] ?? $_ENV[$value] ?? false;

        if ($env === false) {
            throw new ReplacerException('The environment variable "' . $value . '" does not exist');
        }

        if ($env === 'true' || $env === '(true)') {
            return true;
        }

        if ($env === 'false' || $env === '(false)') {
            return false;
        }

        if ($env === 'empty' || $env === '(empty)') {
            return '';
        }

        if ($env === 'null' || $env === '(null)') {
            return null;
        }

        if (($valueLength = strlen($env)) > 1 && str_starts_with($env, '"') && $env[$valueLength - 1] === '"') {
            return substr($env, 1, -1);
        }

        return $env;
    }
}
