<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncJudge\Modes;

use Milex\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;
use Milex\IntegrationsBundle\Sync\Exception\ConflictUnresolvedException;
use Milex\IntegrationsBundle\Sync\SyncJudge\SyncJudgeInterface;

class BestEvidence implements JudgementModeInterface
{
    use DateComparisonTrait;

    /**
     * @throws ConflictUnresolvedException
     */
    public static function adjudicate(
        InformationChangeRequestDAO $leftChangeRequest,
        InformationChangeRequestDAO $rightChangeRequest
    ): InformationChangeRequestDAO {
        try {
            return HardEvidence::adjudicate($leftChangeRequest, $rightChangeRequest);
        } catch (ConflictUnresolvedException $exception) {
        }

        if (null === $leftChangeRequest->getPossibleChangeDateTime() || null === $rightChangeRequest->getPossibleChangeDateTime()) {
            throw new ConflictUnresolvedException();
        }

        $possibleChangeCompare = self::compareDateTimes(
            $leftChangeRequest->getPossibleChangeDateTime(),
            $rightChangeRequest->getPossibleChangeDateTime()
        );

        if (SyncJudgeInterface::NO_WINNER === $possibleChangeCompare) {
            throw new ConflictUnresolvedException();
        }

        if (SyncJudgeInterface::LEFT_WINNER === $possibleChangeCompare) {
            return $leftChangeRequest;
        }

        return $rightChangeRequest;
    }
}
