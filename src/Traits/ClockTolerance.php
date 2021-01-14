<?php

namespace OpenIDConnect\Traits;

trait ClockTolerance
{
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
