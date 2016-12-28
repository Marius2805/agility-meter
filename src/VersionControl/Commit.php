<?php
namespace Marius2805\AgilityMeter\Src\VersionControl;

use Carbon\Carbon;

/**
 * Class Commit
 * @package Marius2805\AgilityMeter\VersionControl
 */
class Commit
{
    /**
     * @var Carbon
     */
    private $date;

    /**
     * @var string
     */
    private $hash;

    /**
     * Commit constructor.
     * @param Carbon $date
     * @param string $hash
     */
    public function __construct(string $hash, Carbon $date)
    {
        $this->date = $date;
        $this->hash = $hash;
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }
}