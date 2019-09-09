<?php

namespace Tests\OpenIDConnect\Metadata;

use DomainException;
use OpenIDConnect\Metadata\ClientRegistration;
use RuntimeException;
use Tests\TestCase;

class ClientRegistrationTest extends TestCase
{
    public function missionRequiredField()
    {
        return array_map(static function ($v) {
            return [$v];
        }, array_keys($this->createClientRegistrationConfig()));
    }

    /**
     * @dataProvider missionRequiredField
     * @test
     */
    public function shouldThrowExceptionReturnAndCanUseIssetWithInstance($missingField): void
    {
        $this->expectException(RuntimeException::class);

        $data = $this->createClientRegistrationConfig();
        unset($data[$missingField]);

        new ClientRegistration($data);
    }

    /**
     * @test
     */
    public function shouldReturnAndCanUseIssetWithInstance(): void
    {
        $target = $this->createClientRegistration();

        $this->assertTrue(isset($target['client_id']));
        $this->assertSame('some_id', $target['client_id']);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenSetValueOnInstance(): void
    {
        $this->expectException(DomainException::class);

        $target = $this->createClientRegistration();

        $target['client_id'] = 'whatever';
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenUnsetValueOnInstance(): void
    {
        $this->expectException(DomainException::class);

        $target = $this->createClientRegistration();

        unset($target['client_id']);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAssertNotExistConfig(): void
    {
        $this->expectException(RuntimeException::class);

        $target = new ClientRegistration($this->createClientRegistrationConfig());

        $target->assertKey('not-exist');
    }

    /**
     * @test
     */
    public function shouldExpectedArrayKeyWhenCallJsonSerialize(): void
    {
        $target = $this->createClientRegistration();

        $actual = $target->jsonSerialize();

        $this->assertArrayHasKey('client_id', $actual);
        $this->assertArrayHasKey('client_secret', $actual);
        $this->assertArrayHasKey('redirect_uris', $actual);
    }

    /**
     * @test
     */
    public function shouldExpectedArrayKeyWhenCallToArray(): void
    {
        $target = $this->createClientRegistration();

        $actual = $target->toArray();

        $this->assertArrayHasKey('client_id', $actual);
        $this->assertArrayHasKey('client_secret', $actual);
        $this->assertArrayHasKey('redirect_uris', $actual);
    }
}
