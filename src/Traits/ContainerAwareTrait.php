<?php

declare(strict_types=1);

namespace Zaphyr\Config\Traits;

use Psr\Container\ContainerInterface;
use Zaphyr\Config\Contracts\ContainerAwareInterface;
use Zaphyr\Config\Exceptions\ConfigException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
trait ContainerAwareTrait
{
    /**
     * @var ContainerInterface|null
     */
    protected ContainerInterface|null $container = null;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container): static
    {
        $this->container = $container;

        if ($this instanceof ContainerAwareInterface) {
            return $this;
        }

        throw new ConfigException(
            'Attempt to use "' . ContainerAwareTrait::class . '" without implementing "' .
            ContainerAwareInterface::class . '"'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ContainerInterface|null
    {
        return $this->container;
    }
}
