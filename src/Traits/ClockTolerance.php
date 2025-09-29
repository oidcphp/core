<?php

namespace OpenIDConnect\Traits;

use Psr\Clock\ClockInterface;

trait ClockTolerance
{
    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var int
     */
    private $clockTolerance = 10;

    /**
     * @return int
     */
    public function clockTolerance(): int
    {
        return $this->clockTolerance;
    }

    /**
     * @param int $clockTolerance
     */
    public function setClockTolerance(int $clockTolerance): void
    {
        $this->clockTolerance = $clockTolerance;
    }
}
