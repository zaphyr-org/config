<?php

declare(strict_types=1);

namespace Zaphyr\Config\Contracts;

use Zaphyr\Config\Exceptions\ReplacerException;

/**
 * Interface ReplacerInterface.
 *
 * @author  merloxx <merloxx@zaphyr.org>
 */
interface ReplacerInterface
{
    /**
     * @param string $value
     *
     * @throws ReplacerException
     *
     * @return mixed
     */
    public function replace(string $value);
}
