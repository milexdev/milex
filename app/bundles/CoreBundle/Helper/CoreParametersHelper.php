<?php

namespace Milex\CoreBundle\Helper;

use Milex\CoreBundle\Loader\ParameterLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class CoreParametersHelper.
 */
class CoreParametersHelper
{
    /**
     * @var ParameterBag
     */
    private $parameters;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $resolvedParameters;

    public function __construct(ContainerInterface $container)
    {
        $loader = new ParameterLoader();

        $this->parameters = $loader->getParameterBag();
        $this->container  = $container;

        $this->resolveParameters();
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $name = $this->stripMilexPrefix($name);

        if ('db_table_prefix' === $name && defined('MILEX_TABLE_PREFIX')) {
            //use the constant in case in the installer
            return MILEX_TABLE_PREFIX;
        }

        // First check the container so that Symfony will resolve container parameters within Milex config values
        $containerName = sprintf('milex.%s', $name);
        if ($this->container->hasParameter($containerName)) {
            return $this->container->getParameter($containerName);
        }

        return $this->parameters->get($name, $default);
    }

    /**
     * @param string $name
     */
    public function has($name): bool
    {
        return $this->parameters->has($this->stripMilexPrefix($name));
    }

    public function all(): array
    {
        return $this->resolvedParameters;
    }

    /**
     * @deprecated 3.0.0 to be removed in 4.0; use get() instead
     */
    public function getParameter($name, $default = null)
    {
        return $this->get($name, $default);
    }

    private function stripMilexPrefix(string $name): string
    {
        return str_replace('milex.', '', $name);
    }

    private function resolveParameters(): void
    {
        $all = $this->parameters->all();

        foreach ($all as $key => $value) {
            $this->resolvedParameters[$key] = $this->get($key, $value);
        }
    }
}
