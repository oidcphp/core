<?php

namespace Tests\Core\Metadata;

use DomainException;
use OpenIDConnect\Metadata\ProviderMetadata;
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
     * @test
     */
    public function shouldThrowExceptionReturnAndCanUseIssetWithInstance($missingField): void
    {
        $this->expectException(OutOfBoundsException::class);

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
     * @test
     */
    public function shouldThrowExceptionWhenSetValueOnInstance(): void
    {
        $this->expectException(DomainException::class);

        $target = new ProviderMetadata($this->createProviderMetadataConfig());

        $target['issuer'] = 'whatever';
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenUnsetValueOnInstance(): void
    {
        $this->expectException(DomainException::class);

        $target = new ProviderMetadata($this->createProviderMetadataConfig());

        unset($target['issuer']);
    }
}
