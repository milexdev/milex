<?php

declare(strict_types=1);

namespace Milex\UserBundle\Tests\Functional\Controller;

use Milex\CoreBundle\Test\MilexMysqlTestCase;

class PublicControllerTest extends MilexMysqlTestCase
{
    /**
     * Tests to ensure that xss is prevented on password reset page.
     */
    public function testXssFilterOnPasswordReset(): void
    {
        $this->client->request('GET', '/passwordreset?bundle=%27-alert("XSS%20TEST%20Milex")-%27');
        $clientResponse = $this->client->getResponse();
        $this->assertSame(200, $clientResponse->getStatusCode(), 'Return code must be 200.');
        $responseData = $clientResponse->getContent();
        // Tests that actual string is not present.
        $this->assertStringNotContainsString('-alert("xss test milex")-', $responseData, 'XSS injection attempt is filtered.');
        // Tests that sanitized string is passed.
        $this->assertStringContainsString('alertxsstestmilex', $responseData, 'XSS sanitized string is present.');
    }
}
