<?php

declare(strict_types=1);

namespace Zaphyr\Config\Contracts;

use Zaphyr\Config\Exceptions\ReplacerException;

/**
 * Interface ReplacerInterface
 *
 * @package Zaphyr\Config\Contracts
 * @author  merloxx <hello@merloxx.it>
 */
interface ReplacerInterface
{
    /**
     * @param string $value
     *
     * @return mixed
     *
     * @throws ReplacerException
     */
    public function replace(string $value);
}
