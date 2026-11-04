<?php

namespace Tests\Feature;

use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Serializer\CompactSerializer;
use MilesChou\Psr\Http\Client\Testing\MockClient;
use OpenIDConnect\Client;
use OpenIDConnect\Config;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\Factory as JwtFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientHandleOpenIDConnectCallbackTest extends TestCase
{
    #[Test]
    public function shouldReturnTokenSetWhenEverythingOkay(): void
    {
        $providerMetadata = $this->createProviderMetadata([
            'issuer' => 'some-iss',
        ]);

        $clientMetadata = $this->createClientMetadata([
            'client_id' => 'some-aud',
        ]);

        $payload = [
            'aud' => 'some-aud',
            'exp' => time() + 3600,
            'iat' => time(),
            'iss' => 'some-iss',
            'nonce' => '0123456789',
            'sub' => 'some-sub',
        ];

        $factory = new JwtFactory(new Config($providerMetadata, $clientMetadata));

        $expectedIdToken = $factory->createSerializeJws($payload);

        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['id_token' => $expectedIdToken])
        );

        $target = new Client(new Config($providerMetadata, $clientMetadata), $mockClient);

        $actual = $target->handleCallback([
            'code' => 'some-code',
        ], [
            'redirect_uri' => 'https://someredirect',
            'nonce' => '0123456789',
        ]);

        $this->assertSame('some-access-token', $actual->accessToken());
        $this->assertSame(3600, $actual->expiresIn());
        $this->assertSame('some-refresh-token', $actual->refreshToken());
        $this->assertSame(['some-scope'], $actual->scope());
        $this->assertSame($expectedIdToken, $actual->idToken());

        $claims = $actual->idTokenClaims();

        $this->assertSame('some-aud', $claims->aud());
        $this->assertSame('some-iss', $claims->iss());
        $this->assertSame('0123456789', $claims->nonce());
        $this->assertSame('some-sub', $claims->sub());
    }

    #[Test]
    public function shouldReturnTokenSetWhenNonceIsNotMatch(): void
    {
        $this->expectException(RelyingPartyException::class);

        $providerMetadata = $this->createProviderMetadata();

        $clientMetadata = $this->createClientMetadata([
            'client_id' => 'some-aud',
        ]);

        $payload = [
            'aud' => 'some-aud',
            'exp' => time() + 3600,
            'iat' => time(),
            'iss' => 'some-iss',
            'nonce' => '0123456789',
            'sub' => 'some-sub',
        ];

        $factory = new JwtFactory(new Config($providerMetadata, $clientMetadata));

        $jws = $factory->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature(JWKFactory::createFromValues($providerMetadata->jwkSet()->get(0)), ['alg' => 'RS256'])
            ->build();

        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['id_token' => (new CompactSerializer())->serialize($jws)])
        );

        $target = new Client(new Config($providerMetadata, $clientMetadata), $mockClient);

        $actual = $target->handleCallback([
            'code' => 'some-code',
        ], [
            'redirect_uri' => 'https://someredirect',
            'nonce' => 'something-another',
        ]);

        $actual->idTokenClaims();
    }

    #[Test]
    public function shouldThrowExceptionWhenNonceClaimIsNotMatch(): void
    {
        $this->expectException(RelyingPartyException::class);

        [$target] = $this->createClient(['nonce' => 'not-the-nonce-you-sent']);

        $target->handleCallback(['code' => 'some-code'], [
            'redirect_uri' => 'https://someredirect',
            'nonce' => 'expected-nonce',
        ]);
    }

    #[Test]
    public function shouldThrowExceptionWhenNonceClaimIsMissingButNonceWasSent(): void
    {
        $this->expectException(RelyingPartyException::class);

        [$target] = $this->createClient();

        $target->handleCallback(['code' => 'some-code'], [
            'redirect_uri' => 'https://someredirect',
            'nonce' => 'expected-nonce',
        ]);
    }

    #[Test]
    public function shouldNotSendNonceToTokenEndpoint(): void
    {
        [$target, $mockClient] = $this->createClient(['nonce' => 'expected-nonce']);

        $target->handleCallback(['code' => 'some-code'], [
            'redirect_uri' => 'https://someredirect',
            'nonce' => 'expected-nonce',
        ]);

        $this->assertStringNotContainsString('nonce', (string)$mockClient->testRequest()->getBody());
    }

    /**
     * @param array<string, mixed> $claims Additional claims of the ID Token
     * @return array{Client, MockClient}
     */
    private function createClient(array $claims = []): array
    {
        $providerMetadata = $this->createProviderMetadata(['issuer' => 'some-iss']);
        $clientMetadata = $this->createClientMetadata(['client_id' => 'some-aud']);

        $config = new Config($providerMetadata, $clientMetadata);

        $idToken = (new JwtFactory($config))->createSerializeJws(array_merge([
            'aud' => 'some-aud',
            'exp' => time() + 3600,
            'iat' => time(),
            'iss' => 'some-iss',
            'sub' => 'some-sub',
        ], $claims));

        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['id_token' => $idToken])
        );

        return [new Client($config, $mockClient), $mockClient];
    }
}
