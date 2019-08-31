<?php

namespace Tests\Core\Metadata;

use DomainException;
use OpenIDConnect\Metadata\ClientMetadata;
use RuntimeException;
use Tests\TestCase;

class ClientMetadataTest extends TestCase
{
    public function missionRequiredField()
    {
        return array_map(static function ($v) {
            return [$v];
        }, array_keys($this->createClientMetadataConfig()));
    }

    /**
     * @dataProvider missionRequiredField
     * @test
     */
    public function shouldThrowExceptionReturnAndCanUseIssetWithInstance($missingField): void
    {
        $this->expectException(RuntimeException::class);

        $data = $this->createClientMetadataConfig();
        unset($data[$missingField]);

        new ClientMetadata($data);
    }

    /**
     * @test
     */
    public function shouldReturnAndCanUseIssetWithInstance(): void
    {
        $target = $this->createClientMetadata();

        $this->assertTrue(isset($target['client_id']));
        $this->assertSame('some_id', $target['client_id']);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenSetValueOnInstance(): void
    {
        $this->expectException(DomainException::class);

        $target = $this->createClientMetadata();

        $target['client_id'] = 'whatever';
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenUnsetValueOnInstance(): void
    {
        $this->expectException(DomainException::class);

        $target = $this->createClientMetadata();

        unset($target['client_id']);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAssertNotExistConfig(): void
    {
        $this->expectException(RuntimeException::class);

        $target = new ClientMetadata($this->createClientMetadataConfig());

        $target->assertKey('not-exist');
    }

    /**
     * @test
     */
    public function shouldExpectedArrayKeyWhenCallJsonSerialize(): void
    {
        $target = $this->createClientMetadata();

        $actual = $target->jsonSerialize();

        $this->assertArrayHasKey('client_id', $actual);
        $this->assertArrayHasKey('client_secret', $actual);
        $this->assertArrayHasKey('redirect_uri', $actual);
    }

    /**
     * @test
     */
    public function shouldExpectedArrayKeyWhenCallToArray(): void
    {
        $target = $this->createClientMetadata();

        $actual = $target->toArray();

        $this->assertArrayHasKey('client_id', $actual);
        $this->assertArrayHasKey('client_secret', $actual);
        $this->assertArrayHasKey('redirect_uri', $actual);
    }
}
