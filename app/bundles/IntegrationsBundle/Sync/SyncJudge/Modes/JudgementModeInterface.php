<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncJudge\Modes;

use Milex\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;

interface JudgementModeInterface
{
    public static function adjudicate(
        InformationChangeRequestDAO $leftChangeRequest,
        InformationChangeRequestDAO $rightChangeRequest
    ): InformationChangeRequestDAO;
}
