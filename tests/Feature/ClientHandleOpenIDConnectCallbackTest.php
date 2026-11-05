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
use OpenIDConnect\Jwt\Factory as JwtFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientHandleOpenIDConnectCallbackTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    private function context(): AuthenticationRequestContext
    {
        return new AuthenticationRequestContext('https://someredirect', 'expected-state', 'expected-nonce');
    }

    private function callbackUri(array $query = ['code' => 'some-code', 'state' => 'expected-state']): Uri
    {
        return new Uri('https://someredirect?' . http_build_query($query));
    }

    /**
     * @param array<mixed> $claims Overwrite the claims of the ID Token. A null value removes the claim.
     */
    private function createClient(array $claims = []): array
    {
        $providerMetadata = $this->createProviderMetadata(['issuer' => 'some-iss']);
        $clientMetadata = $this->createClientMetadata(['client_id' => 'some-aud']);

        $this->config = new Config($providerMetadata, $clientMetadata);

        $payload = array_filter(array_merge([
            'aud' => 'some-aud',
            'exp' => time() + 3600,
            'iat' => time(),
            'iss' => 'some-iss',
            'nonce' => 'expected-nonce',
            'sub' => 'some-sub',
        ], $claims), static fn ($value) => null !== $value);

        $idToken = (new JwtFactory($this->config))->createSerializeJws($payload);

        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['id_token' => $idToken])
        );

        return [new Client($this->config, $mockClient), $mockClient, $idToken];
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
    public function shouldThrowExceptionWhenNonceClaimIsNotMatch(): void
    {
        $this->expectException(RelyingPartyException::class);

        [$target] = $this->createClient(['nonce' => 'not-the-nonce-you-sent']);

        $target->handleCallback($this->callbackUri(), $this->context());
    }

    #[Test]
    public function shouldThrowExceptionWhenNonceClaimIsMissing(): void
    {
        $this->expectException(RelyingPartyException::class);

        [$target] = $this->createClient(['nonce' => null]);

        $target->handleCallback($this->callbackUri(), $this->context());
    }

    #[Test]
    public function shouldThrowExceptionWhenStateIsNotMatch(): void
    {
        $this->expectException(OAuth2ClientException::class);

        [$target] = $this->createClient();

        $target->handleCallback($this->callbackUri(['code' => 'some-code', 'state' => 'other']), $this->context());
    }

    #[Test]
    public function shouldThrowExceptionWhenStateIsMissing(): void
    {
        $this->expectException(OAuth2ClientException::class);

        [$target] = $this->createClient();

        $target->handleCallback($this->callbackUri(['code' => 'some-code']), $this->context());
    }

    #[Test]
    public function shouldThrowExceptionWhenStateIsArray(): void
    {
        $this->expectException(OAuth2ClientException::class);

        [$target] = $this->createClient();

        $uri = new Uri('https://someredirect?code=some-code&state[]=expected-state');

        $target->handleCallback($uri, $this->context());
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
}
