<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncJudge\Modes;

use Milex\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;
use Milex\IntegrationsBundle\Sync\Exception\ConflictUnresolvedException;

class FuzzyEvidence implements JudgementModeInterface
{
    /**
     * @throws ConflictUnresolvedException
     */
    public static function adjudicate(
        InformationChangeRequestDAO $leftChangeRequest,
        InformationChangeRequestDAO $rightChangeRequest
    ): InformationChangeRequestDAO {
        try {
            return BestEvidence::adjudicate($leftChangeRequest, $rightChangeRequest);
        } catch (ConflictUnresolvedException $exception) {
        }

        if (
            $leftChangeRequest->getCertainChangeDateTime() &&
            $rightChangeRequest->getPossibleChangeDateTime() &&
            $leftChangeRequest->getCertainChangeDateTime() > $rightChangeRequest->getPossibleChangeDateTime()
        ) {
            return $leftChangeRequest;
        }

        if (
            $rightChangeRequest->getCertainChangeDateTime() &&
            $leftChangeRequest->getPossibleChangeDateTime() &&
            $rightChangeRequest->getCertainChangeDateTime() > $leftChangeRequest->getPossibleChangeDateTime()
        ) {
            return $rightChangeRequest;
        }

        throw new ConflictUnresolvedException();
    }
}
