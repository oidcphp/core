<?php

namespace Tests\Core;

use BadMethodCallException;
use OpenIDConnect\ProviderMetadata;
use OutOfBoundsException;
use Tests\TestCase;

class ProviderMetadataTest extends TestCase
{
    private const TEST_WORK_DATA = [
        'issuer' => 'https://somewhere',
        'authorization_endpoint' => 'https://somewhere/auth',
        'token_endpoint' => 'https://somewhere/token',
        'jwks_uri' => 'https://somewhere/certs',
        'response_types_supported' => ['code'],
        'subject_types_supported' => ['public'],
        'id_token_signing_alg_values_supported' => ['RS256'],
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

        new ProviderMetadata($data);
    }

    /**
     * @test
     */
    public function shouldReturnAndCanUseIssetWithInstance(): void
    {
        $target = new ProviderMetadata(self::TEST_WORK_DATA);

        $this->assertTrue(isset($target['issuer']));
        $this->assertSame('https://somewhere', $target['issuer']);
    }

    /**
     * @expectedException BadMethodCallException
     * @test
     */
    public function shouldThrowExceptionWhenSetValueOnInstance(): void
    {
        $target = new ProviderMetadata(self::TEST_WORK_DATA);

        $target['issuer'] = 'whatever';
    }

    /**
     * @expectedException BadMethodCallException
     * @test
     */
    public function shouldThrowExceptionWhenUnsetValueOnInstance(): void
    {
        $target = new ProviderMetadata(self::TEST_WORK_DATA);

        unset($target['issuer']);
    }
}
