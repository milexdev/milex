<?php

namespace MilexPlugin\MilexCrmBundle\Tests\Pipedrive\Mock;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use MilexPlugin\MilexCrmBundle\Tests\Pipedrive\PipedriveTest;
use Psr\Http\Message\ResponseInterface;

class Client extends GuzzleClient
{
    public function request($method, $uri = '', array $options = []): ResponseInterface
    {
        //it's hack, there is no option to pass information using class variable in Milex...
        $GLOBALS['requests'][$method.'/'.$uri][] = $options;

        return new Response(200, [], PipedriveTest::getData($uri));
    }
}
