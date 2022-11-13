<?php

namespace Milex\CoreBundle\Tests\Unit\Loader\EnvVars;

use Milex\CoreBundle\Loader\EnvVars\ConfigEnvVars;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class ConfigEnvVarsTest extends TestCase
{
    /**
     * @var ParameterBag
     */
    protected $config;

    /**
     * @var ParameterBag
     */
    protected $defaultConfig;

    /**
     * @var ParameterBag
     */
    protected $envVars;

    protected function setUp(): void
    {
        $this->config        = new ParameterBag();
        $this->defaultConfig = new ParameterBag();
        $this->envVars       = new ParameterBag();
    }

    public function testGetEnvWorks()
    {
        putenv('MILEX_FOOBAR=bar');
        $this->config->set('foo', 'getenv(MILEX_FOOBAR)');

        ConfigEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('bar', $this->envVars->get('MILEX_FOO'));
    }

    public function testLocalValueIsSet()
    {
        $this->config->set('foo', 'bar');

        ConfigEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('bar', $this->envVars->get('MILEX_FOO'));
    }

    public function testValueIsJsonEncodedIfArray()
    {
        $this->config->set('foo', ['bar']);

        ConfigEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('["bar"]', $this->envVars->get('MILEX_FOO'));
    }

    public function testDefaultValueIsJsonEncodedIfArray()
    {
        $this->config->set('foo', null);
        $this->defaultConfig->set('foo', ['bar']);

        ConfigEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('["bar"]', $this->envVars->get('MILEX_FOO'));
    }
}
