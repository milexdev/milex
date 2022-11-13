<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Integration\Interfaces;

interface ConfigFormCallbackInterface
{
    /**
     * Message ID used in form as description what for is used callback URL.
     */
    public function getCallbackHelpMessageTranslationKey(): string;
}
