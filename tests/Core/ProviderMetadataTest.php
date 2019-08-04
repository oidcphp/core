<?php

namespace Tests\Core;

use BadMethodCallException;
use OpenIDConnect\ProviderMetadata;
use OutOfBoundsException;
use Tests\TestCase;

class ProviderMetadataTest extends TestCase
{
    public function missionRequiredField()
    {
        return array_map(static function ($v) {
            return [$v];
        }, array_keys($this->createProviderMetadataConfig()));
    }

    /**
     * @dataProvider missionRequiredField
     * @expectedException OutOfBoundsException
     * @test
     */
    public function shouldThrowExceptionReturnAndCanUseIssetWithInstance($missingField): void
    {
        $data = $this->createProviderMetadataConfig();
        unset($data[$missingField]);

        new ProviderMetadata($data);
    }

    /**
     * @test
     */
    public function shouldReturnAndCanUseIssetWithInstance(): void
    {
        $target = new ProviderMetadata($this->createProviderMetadataConfig());

        $this->assertTrue(isset($target['issuer']));
        $this->assertSame('https://somewhere', $target['issuer']);
    }

    /**
     * @expectedException BadMethodCallException
     * @test
     */
    public function shouldThrowExceptionWhenSetValueOnInstance(): void
    {
        $target = new ProviderMetadata($this->createProviderMetadataConfig());

        $target['issuer'] = 'whatever';
    }

    /**
     * @expectedException BadMethodCallException
     * @test
     */
    public function shouldThrowExceptionWhenUnsetValueOnInstance(): void
    {
        $target = new ProviderMetadata($this->createProviderMetadataConfig());

        unset($target['issuer']);
    }
}
