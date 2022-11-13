<?php

namespace Milex\CoreBundle\Tests\Unit\Loader\EnvVars;

use Milex\CoreBundle\Loader\EnvVars\SAMLEnvVars;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class SAMLEnvVarsTest extends TestCase
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

    public function testEntityIdIsSetToConfigIfNotEmpty()
    {
        $this->config->set('saml_idp_entity_id', 'foobar');
        SAMLEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('foobar', $this->envVars->get('MILEX_SAML_ENTITY_ID'));
    }

    public function testEntityIdIsSetToSiteUrlIfNotEmpty()
    {
        $this->config->set('saml_idp_entity_id', '');
        $this->config->set('site_url', 'https://foobar.com/happydays');

        SAMLEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('https://foobar.com', $this->envVars->get('MILEX_SAML_ENTITY_ID'));
    }

    public function testEntityIdIsSetToMilexByDefault()
    {
        $this->config->set('saml_idp_entity_id', '');
        $this->config->set('site_url', '');

        SAMLEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('milex', $this->envVars->get('MILEX_SAML_ENTITY_ID'));
    }

    public function testLoginPathIsDefaultIfSamlIsDisabled()
    {
        $this->config->set('saml_idp_metadata', 'enabled');

        SAMLEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('/s/saml/login', $this->envVars->get('MILEX_SAML_LOGIN_PATH'));
        $this->assertEquals('/s/saml/login_check', $this->envVars->get('MILEX_SAML_LOGIN_CHECK_PATH'));
    }

    public function testCorrectLoginPathIfSamlIsEnabled()
    {
        $this->config->set('saml_idp_metadata', '');

        SAMLEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('/s/login', $this->envVars->get('MILEX_SAML_LOGIN_PATH'));
        $this->assertEquals('/s/login_check', $this->envVars->get('MILEX_SAML_LOGIN_CHECK_PATH'));
    }
}
