<?php

declare(strict_types=1);

namespace Tests\Unit\Jwt\Verifiers;

use OpenIDConnect\Config;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\Factory as JwtFactory;
use OpenIDConnect\Jwt\Verifiers\IdTokenVerifier;
use OpenIDConnect\Jwt\Verifiers\LogoutTokenVerifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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

    public static function validPayload(): iterable
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

        // iat a few seconds in the future, within the default clock tolerance
        yield [
            [
                'iss' => 'some-iss',
                'aud' => 'some-aud',
                'exp' => time() + 3600,
                'iat' => time() + 5,
                'sub' => 'some-sub',
            ],
        ];
    }

    #[Test]
    #[DataProvider('validPayload')]
    public function shouldBeOkayWhenVerifyValidIdToken($validPayload): void
    {
        $this->expectNotToPerformAssertions();

        $factory = new JwtFactory($this->config);

        $this->target->verify(
            $factory->createSerializeJws($validPayload)
        );
    }

    public static function invalidTimePayload(): iterable
    {
        // Expired exp
        yield [['exp' => time() - 3600, 'iat' => time() - 7200]];

        // iat in the future
        yield [['exp' => time() + 7200, 'iat' => time() + 3600]];
    }

    #[Test]
    #[DataProvider('invalidTimePayload')]
    public function shouldThrowExceptionWhenTimeClaimIsInvalid($timeClaims): void
    {
        $this->expectException(RelyingPartyException::class);

        $factory = new JwtFactory($this->config);

        $this->target->verify(
            $factory->createSerializeJws(['iss' => 'some-iss', 'aud' => 'some-aud', 'sub' => 'some-sub'] + $timeClaims)
        );
    }
}
