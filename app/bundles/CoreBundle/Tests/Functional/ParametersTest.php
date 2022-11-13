<?php

declare(strict_types=1);

namespace Milex\CoreBundle\Tests\Functional;

use Milex\CoreBundle\Test\AbstractMilexTestCase;
use PHPUnit\Framework\Assert;

class ParametersTest extends AbstractMilexTestCase
{
    public function testRememberMeParameterUsesIntProcessor(): void
    {
        Assert::assertSame(31536000, self::$container->getParameter('milex.rememberme_lifetime'));
    }
}
