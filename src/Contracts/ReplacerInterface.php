<?php

declare(strict_types=1);

namespace Zaphyr\Config\Contracts;

use Zaphyr\Config\Exceptions\ReplacerException;

/**
 * @author merloxx <merloxx@zaphyr.org>
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
