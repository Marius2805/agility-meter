<?php
namespace Marius2805\AgilityMeter\Src\Agility;

use Carbon\Carbon;
use Marius2805\AgilityMeter\Src\VersionControl\Commit;

/**
 * Class TimeFrameFactory
 * @package Marius2805\AgilityMeter\Src\Agility
 */
class TimeFrameFactory
{
    /**
     * @param Commit $start
     * @return TimeFrame[]
     */
    public function getMonthInterval(Commit $start) : array
    {
        $timeFrames = [];
        $refDate = (clone $start->getDate())->startOfMonth();

        while ($refDate <= Carbon::today()->startOfMonth()) {
            $timeFrames[] = new TimeFrame($refDate->format('M y'), (clone $refDate), (clone $refDate)->endOfMonth());
            $refDate->addMonths(1);
        }

        return $timeFrames;
    }
}