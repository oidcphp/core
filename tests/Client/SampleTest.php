<?php

namespace Tests\Client;

use OpenIDConnect\Client\Sample;
use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    public function testSample()
    {
        $this->assertTrue((new Sample())->alwaysTrue());
    }
}
