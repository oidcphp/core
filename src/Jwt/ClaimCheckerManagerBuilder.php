<?php

namespace OpenIDConnect\Jwt;

use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\ClaimCheckerManager;

class ClaimCheckerManagerBuilder
{
    /**
     * @var ClaimChecker[]
     */
    private $checkers = [];

    /**
     * @param string $checker
     * @param mixed ...$parameters
     * @return ClaimCheckerManagerBuilder
     */
    public function add(string $checker, ...$parameters): ClaimCheckerManagerBuilder
    {
        if (!is_subclass_of($checker, ClaimChecker::class)) {
            throw new \InvalidArgumentException("Checker class '{$checker}' is not subclass of ClaimChecker");
        }

        $this->checkers[] = new $checker(...$parameters);

        return $this;
    }

    /**
     * Build ClaimCheckerManager instance with checkers.
     *
     * @return ClaimCheckerManager
     */
    public function build(): ClaimCheckerManager
    {
        return new ClaimCheckerManager($this->checkers);
    }
}
