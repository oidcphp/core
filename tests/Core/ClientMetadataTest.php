<?php

namespace Tests\Core;

use BadMethodCallException;
use OpenIDConnect\ClientMetadata;
use OutOfBoundsException;
use Tests\TestCase;

class ClientMetadataTest extends TestCase
{
    private const TEST_WORK_DATA = [
        'client_id' => 'some_id',
        'client_secret' => 'some_secret',
        'redirect_uri' => 'https://someredirect',
    ];

    public function missionRequiredField()
    {
        return array_map(static function ($v) {
            return [$v];
        }, array_keys(self::TEST_WORK_DATA));
    }

    /**
     * @dataProvider missionRequiredField
     * @expectedException OutOfBoundsException
     * @test
     */
    public function shouldThrowExceptionReturnAndCanUseIssetWithInstance($missingField): void
    {
        $data = self::TEST_WORK_DATA;
        unset($data[$missingField]);

        new ClientMetadata($data);
    }

    /**
     * @test
     */
    public function shouldReturnAndCanUseIssetWithInstance(): void
    {
        $target = new ClientMetadata(self::TEST_WORK_DATA);

        $this->assertTrue(isset($target['client_id']));
        $this->assertSame('some_id', $target['client_id']);
    }

    /**
     * @expectedException BadMethodCallException
     * @test
     */
    public function shouldThrowExceptionWhenSetValueOnInstance(): void
    {
        $target = new ClientMetadata(self::TEST_WORK_DATA);

        $target['client_id'] = 'whatever';
    }

    /**
     * @expectedException BadMethodCallException
     * @test
     */
    public function shouldThrowExceptionWhenUnsetValueOnProviderMetadataInstance(): void
    {
        $target = new ClientMetadata(self::TEST_WORK_DATA);

        unset($target['client_id']);
    }
}
