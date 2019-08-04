<?php

namespace Tests\Core;

use BadMethodCallException;
use OpenIDConnect\ClientMetadata;
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
     * @expectedException OutOfBoundsException
     * @test
     */
    public function shouldThrowExceptionReturnAndCanUseIssetWithInstance($missingField): void
    {
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
     * @expectedException BadMethodCallException
     * @test
     */
    public function shouldThrowExceptionWhenSetValueOnInstance(): void
    {
        $target = new ClientMetadata($this->createClientMetadataConfig());

        $target['client_id'] = 'whatever';
    }

    /**
     * @expectedException BadMethodCallException
     * @test
     */
    public function shouldThrowExceptionWhenUnsetValueOnInstance(): void
    {
        $target = new ClientMetadata($this->createClientMetadataConfig());

        unset($target['client_id']);
    }

    /**
     * @expectedException OutOfBoundsException
     * @test
     */
    public function shouldThrowExceptionWhenAssertNotExistConfig(): void
    {
        $target = new ClientMetadata($this->createClientMetadataConfig());

        $target->assertConfiguration('not-exist');
    }
}
