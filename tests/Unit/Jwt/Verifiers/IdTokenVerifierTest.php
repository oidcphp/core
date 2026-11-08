<?php

declare(strict_types=1);

namespace Tests\Unit\Jwt\Verifiers;

use OpenIDConnect\Config;
use OpenIDConnect\Exceptions\OpenIDProviderException;
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

    private function createUnsignedToken(array $payload, string $alg = 'none'): string
    {
        $encode = static fn (array $data): string => rtrim(
            strtr(base64_encode(json_encode($data, JSON_THROW_ON_ERROR)), '+/', '-_'),
            '='
        );

        return $encode(['alg' => $alg, 'typ' => 'JWT']) . '.' . $encode($payload) . '.';
    }

    #[Test]
    #[DataProvider('validPayload')]
    public function shouldBeOkayWhenVerifyUnsignedIdTokenAndClientRegisteredNone($validPayload): void
    {
        $target = new IdTokenVerifier($this->createConfig([
            'issuer' => 'some-iss',
        ], [
            'client_id' => 'some-aud',
            'id_token_signed_response_alg' => 'none',
        ]));

        $claims = $target->verifyUnsigned($this->createUnsignedToken($validPayload));

        $this->assertSame('some-sub', $claims->sub());
    }

    #[Test]
    #[DataProvider('validPayload')]
    public function shouldThrowExceptionWhenVerifyUnsignedIdTokenAndClientDidNotRegisterNone($validPayload): void
    {
        $this->expectException(RelyingPartyException::class);

        $this->target->verifyUnsigned($this->createUnsignedToken($validPayload));
    }

    #[Test]
    #[DataProvider('validPayload')]
    public function shouldThrowExceptionWhenVerifyUnsignedIdTokenWithOtherAlg($validPayload): void
    {
        $this->expectException(OpenIDProviderException::class);

        $target = new IdTokenVerifier($this->createConfig([
            'issuer' => 'some-iss',
        ], [
            'client_id' => 'some-aud',
            'id_token_signed_response_alg' => 'none',
        ]));

        $target->verifyUnsigned($this->createUnsignedToken($validPayload, 'RS256'));
    }

    #[Test]
    #[DataProvider('validPayload')]
    public function shouldThrowExceptionWhenVerifyUnsignedIdTokenWithInvalidClaims($validPayload): void
    {
        $this->expectException(RelyingPartyException::class);

        $target = new IdTokenVerifier($this->createConfig([
            'issuer' => 'some-iss',
        ], [
            'client_id' => 'some-aud',
            'id_token_signed_response_alg' => 'none',
        ]));

        $validPayload['iss'] = 'other-iss';

        $target->verifyUnsigned($this->createUnsignedToken($validPayload));
    }

    #[Test]
    #[DataProvider('validPayload')]
    public function shouldAlwaysRejectUnsignedIdTokenWhenUseVerify($validPayload): void
    {
        $this->expectException(OpenIDProviderException::class);

        $target = new IdTokenVerifier($this->createConfig([
            'issuer' => 'some-iss',
        ], [
            'client_id' => 'some-aud',
            'id_token_signed_response_alg' => 'none',
        ]));

        $target->verify($this->createUnsignedToken($validPayload));
    }
}
