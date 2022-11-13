<?php

namespace Milex\CoreBundle\Tests\Unit\Loader;

use Milex\CoreBundle\Loader\ParameterLoader;
use PHPUnit\Framework\TestCase;

class ParameterLoaderTest extends TestCase
{
    public function testParametersAreLoaded(): void
    {
        $envParameters = json_encode(['default_daterange_filter' => '-1 day']);
        putenv('MILEX_CONFIG_PARAMETERS='.$envParameters);

        $loader = new ParameterLoader(__DIR__.'/TestRoot');
        $loader->loadIntoEnvironment();

        $parameterBag = $loader->getParameterBag();

        $this->assertEquals('https://language-packs.milex.com/', $parameterBag->get('translations_fetch_url'));
        $this->assertEquals('https://language-packs.milex.com/', getenv('MILEX_TRANSLATIONS_FETCH_URL'));

        $this->assertEquals('foobar.com', $parameterBag->get('mailer_host'));
        $this->assertEquals('foobar.com', getenv('MILEX_MAILER_HOST'));

        $this->assertEquals('-1 day', $parameterBag->get('default_daterange_filter'));
        $this->assertEquals('-1 day', getenv('MILEX_DEFAULT_DATERANGE_FILTER'));

        putenv('MILEX_CONFIG_PARAMETERS=');
    }

    public function testDefaultParametersAreLoaded(): void
    {
        $loader = new ParameterLoader(__DIR__.'/TestRoot');
        $this->assertIsArray($loader->getDefaultParameters());
        $this->assertFalse($loader->getDefaultParameters()['api_enabled']);
    }
}
