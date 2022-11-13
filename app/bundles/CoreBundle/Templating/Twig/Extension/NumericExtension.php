<?php

declare(strict_types=1);

namespace Milex\CoreBundle\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class NumericExtension extends AbstractExtension
{
    public function getTests()
    {
        return [
            new TwigTest('numeric', fn ($value) => is_numeric($value)),
        ];
    }
}
