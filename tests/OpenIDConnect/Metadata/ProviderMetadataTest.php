<?php

namespace Tests\OpenIDConnect\Metadata;

use DomainException;
use OpenIDConnect\Metadata\ProviderMetadata;
use RuntimeException;
use Tests\TestCase;

class ProviderMetadataTest extends TestCase
{
    public function missingTheRequiredField()
    {
        return array_map(static function ($v) {
            return [$v];
        }, array_keys($this->createProviderMetadataConfig()));
    }

    /**
     * @dataProvider missingTheRequiredField
     * @test
     */
    public function shouldThrowExceptionReturnAndCanUseIssetWithInstance($missingField): void
    {
        $this->expectException(RuntimeException::class);

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

    /**
     * @test
     */
    public function shouldExpectedArrayKeyWhenCallJsonSerialize(): void
    {
        $target = $this->createProviderMetadata();

        $actual = $target->jsonSerialize();

        $this->assertArrayHasKey('discovery', $actual);
        $this->assertArrayHasKey('jwks', $actual);
    }

    /**
     * @test
     */
    public function shouldExpectedArrayKeyWhenCallToArray(): void
    {
        $target = $this->createProviderMetadata();

        $actual = $target->toArray();

        $this->assertArrayHasKey('discovery', $actual);
        $this->assertArrayHasKey('jwks', $actual);
    }
}
