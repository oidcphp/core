<?php

namespace Tests\Feature;

use InvalidArgumentException;
use Laminas\Diactoros\Uri;
use MilesChou\Psr\Http\Client\Testing\MockClient;
use OpenIDConnect\AuthenticationRequestContext;
use OpenIDConnect\Client;
use OpenIDConnect\Config;
use OpenIDConnect\Exceptions\OAuth2ClientException;
use OpenIDConnect\Exceptions\OAuth2ServerException;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Http\Query;
use OpenIDConnect\Jwt\Factory as JwtFactory;
use OpenIDConnect\PkceCodeVerifier;
use OpenIDConnect\UnsafeAuthenticationRequestContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientHandleOpenIDConnectCallbackTest extends TestCase
{
    private function context(): AuthenticationRequestContext
    {
        return new AuthenticationRequestContext('https://someredirect', 'expected-state', 'expected-nonce');
    }

    private function callbackUri(array $query = ['code' => 'some-code', 'state' => 'expected-state']): Uri
    {
        return new Uri('https://someredirect?' . Query::build($query));
    }

    /**
     * @param array<mixed> $claims Overwrite the claims of the ID Token. A null value removes the claim.
     */
    private function createClient(array $claims = []): array
    {
        $providerMetadata = $this->createProviderMetadata(['issuer' => 'some-iss']);
        $clientMetadata = $this->createClientMetadata(['client_id' => 'some-aud']);

        $config = new Config($providerMetadata, $clientMetadata);

        $payload = array_filter(array_merge([
            'aud' => 'some-aud',
            'exp' => time() + 3600,
            'iat' => time(),
            'iss' => 'some-iss',
            'nonce' => 'expected-nonce',
            'sub' => 'some-sub',
        ], $claims), static fn ($value) => null !== $value);

        $idToken = (new JwtFactory($config))->createSerializeJws($payload);

        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['id_token' => $idToken])
        );

        return [new Client($config, $mockClient), $mockClient, $idToken];
    }

    #[Test]
    public function shouldReturnTokenSetWhenEverythingOkay(): void
    {
        [$target, , $expectedIdToken] = $this->createClient();

        $actual = $target->handleCallback($this->callbackUri(), $this->context());

        $this->assertSame('some-access-token', $actual->accessToken());
        $this->assertSame($expectedIdToken, $actual->idToken());

        $claims = $actual->idTokenClaims();

        $this->assertSame('some-aud', $claims->aud());
        $this->assertSame('some-iss', $claims->iss());
        $this->assertSame('expected-nonce', $claims->nonce());
        $this->assertSame('some-sub', $claims->sub());
    }

    #[Test]
    public function shouldSendOnlyTokenRequestParametersToTokenEndpoint(): void
    {
        [$target, $mockClient] = $this->createClient();

        $target->handleCallback($this->callbackUri(), $this->context());

        $request = $mockClient->testRequest();
        $request->assertBodyContains('grant_type=authorization_code');
        $request->assertBodyContains('code=some-code');
        $request->assertBodyContains('redirect_uri=' . rawurlencode('https://someredirect'));

        $body = (string)$request->getBody();
        $this->assertStringNotContainsString('state', $body);
        $this->assertStringNotContainsString('nonce', $body);
    }

    #[Test]
    public function shouldSendCodeVerifierToTokenEndpointWhenPkceIsUsed(): void
    {
        [$target, $mockClient] = $this->createClient();

        $target->handleCallback($this->callbackUri(), new AuthenticationRequestContext(
            'https://someredirect',
            'expected-state',
            'expected-nonce',
            new PkceCodeVerifier('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk')
        ));

        $mockClient->testRequest()->assertBodyContains('code_verifier=dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk');
    }

    #[Test]
    public function shouldNotSendCodeVerifierToTokenEndpointWhenPkceIsNotUsed(): void
    {
        [$target, $mockClient] = $this->createClient();

        $target->handleCallback($this->callbackUri(), $this->context());

        $this->assertStringNotContainsString('code_verifier', (string)$mockClient->testRequest()->getBody());
    }

    #[Test]
    #[DataProvider('invalidNonceClaim')]
    public function shouldThrowExceptionWhenNonceClaimIsInvalid(array $claims): void
    {
        $this->expectException(RelyingPartyException::class);

        [$target] = $this->createClient($claims);

        $target->handleCallback($this->callbackUri(), $this->context());
    }

    public static function invalidNonceClaim(): array
    {
        return [
            'not match' => [['nonce' => 'not-the-nonce-you-sent']],
            'missing' => [['nonce' => null]],
        ];
    }

    #[Test]
    #[DataProvider('invalidState')]
    public function shouldThrowExceptionWhenStateIsInvalid(string $query): void
    {
        $this->expectException(OAuth2ClientException::class);

        [$target] = $this->createClient();

        $target->handleCallback(new Uri('https://someredirect?' . $query), $this->context());
    }

    public static function invalidState(): array
    {
        return [
            'not match' => ['code=some-code&state=other'],
            'missing' => ['code=some-code'],
            'array' => ['code=some-code&state[]=expected-state'],
        ];
    }

    #[Test]
    public function shouldThrowExceptionWhenCodeIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        [$target] = $this->createClient();

        $target->handleCallback($this->callbackUri(['state' => 'expected-state']), $this->context());
    }

    #[Test]
    public function shouldThrowServerExceptionWhenResponseIsError(): void
    {
        $this->expectException(OAuth2ServerException::class);
        $this->expectExceptionMessage('access_denied: The user denied');

        [$target, $mockClient] = $this->createClient();

        try {
            $target->handleCallback($this->callbackUri([
                'error' => 'access_denied',
                'error_description' => 'The user denied',
                'state' => 'expected-state',
            ]), $this->context());
        } finally {
            $this->assertFalse($mockClient->hasRequests(), 'Token Endpoint must not be called');
        }
    }

    #[Test]
    public function shouldNotTrustErrorResponseWhenStateIsNotMatch(): void
    {
        $this->expectException(OAuth2ClientException::class);

        [$target] = $this->createClient();

        $target->handleCallback($this->callbackUri(['error' => 'access_denied', 'state' => 'other']), $this->context());
    }

    #[Test]
    public function shouldSkipStateCheckWhenUnsafeContextHasNoState(): void
    {
        [$target] = $this->createClient();

        $actual = $target->handleCallback(
            $this->callbackUri(['code' => 'some-code']),
            new UnsafeAuthenticationRequestContext('https://someredirect', null, 'expected-nonce')
        );

        $this->assertSame('some-sub', $actual->idTokenClaims()->sub());
    }

    #[Test]
    public function shouldStillCheckStateWhenUnsafeContextHasState(): void
    {
        $this->expectException(OAuth2ClientException::class);

        [$target] = $this->createClient();

        $target->handleCallback(
            $this->callbackUri(['code' => 'some-code', 'state' => 'other']),
            new UnsafeAuthenticationRequestContext('https://someredirect', 'expected-state')
        );
    }

    #[Test]
    public function shouldSkipNonceCheckWhenUnsafeContextHasNoNonce(): void
    {
        [$target] = $this->createClient(['nonce' => null]);

        $actual = $target->handleCallback(
            $this->callbackUri(),
            new UnsafeAuthenticationRequestContext('https://someredirect', 'expected-state')
        );

        $this->assertSame('some-sub', $actual->idTokenClaims()->sub());
    }

    #[Test]
    public function shouldStillCheckNonceWhenUnsafeContextHasNonce(): void
    {
        $this->expectException(RelyingPartyException::class);

        [$target] = $this->createClient(['nonce' => 'not-the-nonce-you-sent']);

        $target->handleCallback(
            $this->callbackUri(),
            new UnsafeAuthenticationRequestContext('https://someredirect', 'expected-state', 'expected-nonce')
        );
    }
}
