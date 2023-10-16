<?php

declare(strict_types=1);

namespace Zaphyr\Config\Contracts;

use Psr\Container\ContainerInterface;
use Zaphyr\Config\Exceptions\ConfigException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface ContainerAwareInterface
{
    /**
     * @param ContainerInterface $container
     *
     * @throws ConfigException if the class does not implement ContainerAwareInterface
     * @return $this
     */
    public function setContainer(ContainerInterface $container): static;

    /**
     * @return ContainerInterface|null
     */
    public function getContainer(): ContainerInterface|null;
}
