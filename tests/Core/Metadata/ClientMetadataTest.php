<?php

namespace Tests\Core\Metadata;

use DomainException;
use OpenIDConnect\Metadata\ClientMetadata;
use OutOfBoundsException;
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
        $this->expectException(OutOfBoundsException::class);

        $data = $this->createClientMetadataConfig();
        unset($data[$missingField]);

        new ClientMetadata($data);
    }

    /**
     * @test
     */
    public function shouldReturnAndCanUseIssetWithInstance(): void
    {
        $target = new ClientMetadata($this->createClientMetadataConfig());

        $this->assertTrue(isset($target['client_id']));
        $this->assertSame('some_id', $target['client_id']);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenSetValueOnInstance(): void
    {
        $this->expectException(DomainException::class);

        $target = new ClientMetadata($this->createClientMetadataConfig());

        $target['client_id'] = 'whatever';
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenUnsetValueOnInstance(): void
    {
        $this->expectException(DomainException::class);

        $target = new ClientMetadata($this->createClientMetadataConfig());

        unset($target['client_id']);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAssertNotExistConfig(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $target = new ClientMetadata($this->createClientMetadataConfig());

        $target->assertConfiguration('not-exist');
    }
}
