<?php

declare(strict_types=1);

namespace Tests\Unit\Jwt\Verifiers;

use OpenIDConnect\Config;
use OpenIDConnect\Jwt\Factory as JwtFactory;
use OpenIDConnect\Jwt\Verifiers\IdTokenVerifier;
use OpenIDConnect\Jwt\Verifiers\LogoutTokenVerifier;
use Tests\TestCase;

class IdTokenVerifierTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LogoutTokenVerifier
     */
    private $target;

    protected function setUp(): void
    {
        $this->config = $this->createConfig([
            'issuer' => 'some-iss',
        ], [
            'client_id' => 'some-aud',
        ]);

        $this->target = new IdTokenVerifier($this->config);
    }

    protected function tearDown(): void
    {
        $this->target = null;
    }

    public function validPayload(): iterable
    {
        yield [
            [
                'iss' => 'some-iss',
                'aud' => 'some-aud',
                'exp' => time() + 3600,
                'iat' => time(),
                'nonce' => '0123456789',
                'sub' => 'some-sub',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validPayload
     */
    public function shouldBeOkayWhenVerifyValidIdToken($validPayload): void
    {
        $this->expectNotToPerformAssertions();

        $factory = new JwtFactory($this->config);

        $this->target->verify(
            $factory->createSerializeJws($validPayload)
        );
    }
}
