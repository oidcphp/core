<?php

namespace Tests\Unit;

use Laminas\Diactoros\Request;
use MilesChou\Psr\Http\Client\Testing\MockClient;
use OpenIDConnect\AuthenticationRequestContext;
use OpenIDConnect\Client;
use OpenIDConnect\Contracts\PkceCodeVerifierInterface;
use OpenIDConnect\Exceptions\OAuth2ServerException;
use OpenIDConnect\PlainPkceCodeVerifier;
use OpenIDConnect\S256PkceCodeVerifier;
use OpenIDConnect\UnsafeAuthenticationRequestContext;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private $target;

    protected function setUp(): void
    {
        $this->target = new Client($this->createConfig([
            'revocation_endpoint' => 'http://some-revocation-endpoint',
        ]), new MockClient());
    }

    protected function tearDown(): void
    {
        $this->target = null;
    }

    #[Test]
    public function shouldReturnPreparedStateWhenInitParameters(): void
    {
        $this->target->initAuthenticationRequestContext([
            'state' => 'expected-state',
        ]);

        $this->assertSame('expected-state', $this->target->getState());
    }

    #[Test]
    public function shouldStillInitContextWhenCallDeprecatedInitAuthorizationParameters(): void
    {
        $this->target->initAuthorizationParameters([
            'state' => 'expected-state',
            'nonce' => 'expected-nonce',
        ]);

        $this->assertSame('expected-state', $this->target->getState());
        $this->assertSame('expected-nonce', $this->target->getNonce());
    }

    #[Test]
    public function shouldReturnHtmlWhenCallCreateFormPost(): void
    {
        $actual = $this->target->createAuthorizeFormPostResponse([
            'redirect_uri' => 'https://someredirect',
        ]);

        $this->assertStringContainsString('action="https://somewhere/auth"', (string)$actual->getBody());
        $this->assertStringContainsString('name="state"', (string)$actual->getBody());
        $this->assertStringContainsString('name="response_type" value="code"', (string)$actual->getBody());
        $this->assertStringContainsString(
            'name="redirect_uri" value="https://someredirect"',
            (string)$actual->getBody()
        );
        $this->assertStringContainsString('name="client_id" value="some_id"', (string)$actual->getBody());
    }

    #[Test]
    public function shouldReturnRedirectWhenCallCreateRedirect(): void
    {
        $actual = $this->target->createAuthorizeRedirectResponse([
            'redirect_uri' => 'https://someredirect',
        ]);

        $actualLocation = $actual->getHeaderLine('Location');

        $this->assertStringStartsWith('https://somewhere/auth', $actualLocation);
        $this->assertStringContainsString('state=', $actualLocation);
        $this->assertStringContainsString('response_type=code', $actualLocation);
        $this->assertStringContainsString('redirect_uri=' . rawurlencode('https://someredirect'), $actualLocation);
        $this->assertStringContainsString('client_id=some_id', $actualLocation);
    }

    #[Test]
    public function shouldReturnTokenSetInstanceWhenCallSendTokenRequest(): void
    {
        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['access_token' => 'some-access-token'])
        );

        $this->target->setHttpClient($mockClient);

        $actual = $this->target->token([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
        ], []);

        $this->assertSame('some-access-token', $actual['access_token']);
    }

    #[Test]
    public function shouldThrowOAuth2ServerExceptionWhenCatchRequestException(): void
    {
        $this->expectException(OAuth2ServerException::class);

        $exception = new class extends \Exception implements NetworkExceptionInterface {
            public function getRequest(): RequestInterface
            {
                return new Request('whatever', 'GET');
            }
        };

        $mockClient = (new MockClient())->appendThrowable($exception);

        $this->target->setHttpClient($mockClient);

        $this->target->token([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
        ], []);
    }

    #[Test]
    public function shouldThrowOAuth2ServerExceptionWhenPayloadIsNotJson(): void
    {
        $this->expectException(OAuth2ServerException::class);

        $mockClient = (new MockClient())->appendResponseWith('not json');

        $this->target->setHttpClient($mockClient);

        $this->target->token([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
        ], []);
    }

    #[Test]
    public function shouldThrowOAuth2ServerExceptionWhenReturnJsonHasErrorKey(): void
    {
        $this->expectException(OAuth2ServerException::class);

        $mockClient = (new MockClient())->appendResponseWithJson(['error' => 'whatever']);

        $this->target->setHttpClient($mockClient);

        $this->target->token([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
        ], []);
    }

    #[Test]
    public function shouldNothingWhenCallRevokeWithOkResponse(): void
    {
        $mockClient = (new MockClient())->appendResponseWith();

        $this->target->setHttpClient($mockClient);

        $this->target->revoke('some-token', ['extra' => 'some-extra']);

        $this->assertSame('POST', $mockClient->requests[0]->getMethod());
        $this->assertSame('http://some-revocation-endpoint', (string)$mockClient->requests[0]->getUri());
    }

    #[Test]
    public function shouldThrowOAuth2ServerExceptionWhenCallRevokeWithCatchRequestException(): void
    {
        $this->expectException(OAuth2ServerException::class);

        $exception = new class extends \Exception implements NetworkExceptionInterface {
            public function getRequest(): RequestInterface
            {
                return new Request('whatever', 'GET');
            }
        };

        $mockClient = (new MockClient())->appendThrowable($exception);

        $this->target->setHttpClient($mockClient);

        $this->target->revoke('whatever');
    }

    #[Test]
    public function shouldThrowOAuth2ServerExceptionWhenCallRevokeWithReturnJsonHasErrorKey(): void
    {
        $this->expectException(OAuth2ServerException::class);

        $mockClient = (new MockClient())->appendResponseWithJson(['error' => 'whatever']);

        $this->target->setHttpClient($mockClient);

        $this->target->revoke('whatever');
    }

    #[Test]
    public function shouldCreateContextWithRandomStateAndNonce(): void
    {
        $a = $this->target->createAuthenticationRequestContext('https://someredirect');
        $b = $this->target->createAuthenticationRequestContext('https://someredirect');

        $this->assertSame('https://someredirect', $a->redirectUri);
        $this->assertSame(32, strlen($a->state));
        $this->assertSame(32, strlen($a->nonce));
        $this->assertNotSame($a->state, $a->nonce);
        $this->assertNotSame($a->state, $b->state);
        $this->assertNotSame($a->nonce, $b->nonce);
    }

    #[Test]
    public function shouldUseValuesOfContextWhenCallCreateAuthorizeRedirectResponse(): void
    {
        $context = new AuthenticationRequestContext('https://context-redirect', 'context-state', 'context-nonce');

        $actualLocation = $this->target->createAuthorizeRedirectResponse([
            'redirect_uri' => 'https://ignored',
        ], $context)->getHeaderLine('Location');

        $this->assertStringContainsString('state=context-state', $actualLocation);
        $this->assertStringContainsString('nonce=context-nonce', $actualLocation);
        $this->assertStringContainsString('redirect_uri=' . rawurlencode('https://context-redirect'), $actualLocation);
        $this->assertStringNotContainsString('ignored', $actualLocation);
    }

    #[Test]
    public function shouldReturnStateAndNonceOfContextWhenCallCreateAuthorizeRedirectResponse(): void
    {
        $this->target->createAuthorizeRedirectResponse([], new AuthenticationRequestContext(
            'https://context-redirect',
            'context-state',
            'context-nonce'
        ));

        $this->assertSame('context-state', $this->target->getState());
        $this->assertSame('context-nonce', $this->target->getNonce());
    }

    #[Test]
    public function shouldCreateContextWithRandomCodeVerifierByDefault(): void
    {
        $a = $this->target->createAuthenticationRequestContext('https://someredirect');
        $b = $this->target->createAuthenticationRequestContext('https://someredirect');

        $this->assertInstanceOf(PkceCodeVerifierInterface::class, $a->codeVerifier);
        $this->assertSame(43, strlen($a->codeVerifier->value()));
        $this->assertNotSame($a->codeVerifier->value(), $b->codeVerifier->value());
    }

    #[Test]
    public function shouldCreateContextWithoutCodeVerifierWhenPkceIsOff(): void
    {
        $actual = $this->target->createAuthenticationRequestContext('https://someredirect', false);

        $this->assertNull($actual->codeVerifier);

        $location = $this->target->createAuthorizeRedirectResponse([], $actual)->getHeaderLine('Location');

        $this->assertStringNotContainsString('code_challenge', $location);
    }

    #[Test]
    public function shouldUseGivenCodeVerifierWhenCreateContext(): void
    {
        $verifier = new S256PkceCodeVerifier('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk');

        $actual = $this->target->createAuthenticationRequestContext('https://someredirect', true, $verifier);

        $this->assertSame($verifier, $actual->codeVerifier);
    }

    #[Test]
    public function shouldAddS256CodeChallengeWhenContextHasCodeVerifier(): void
    {
        // The test vector of RFC 7636 Appendix B
        $context = new AuthenticationRequestContext(
            'https://someredirect',
            'some-state',
            'some-nonce',
            new S256PkceCodeVerifier('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk')
        );

        $location = $this->target->createAuthorizeRedirectResponse([], $context)->getHeaderLine('Location');

        $this->assertStringContainsString('code_challenge=E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM', $location);
        $this->assertStringContainsString('code_challenge_method=S256', $location);
        $this->assertStringNotContainsString('code_verifier', $location);
        $this->assertStringNotContainsString('dBjftJeZ4CVP', $location);
    }

    #[Test]
    public function shouldAddPlainCodeChallengeWhenContextHasPlainCodeVerifier(): void
    {
        $context = new AuthenticationRequestContext(
            'https://someredirect',
            'some-state',
            'some-nonce',
            new PlainPkceCodeVerifier('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk')
        );

        $location = $this->target->createAuthorizeRedirectResponse([], $context)->getHeaderLine('Location');

        $this->assertStringContainsString('code_challenge=dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk', $location);
        $this->assertStringContainsString('code_challenge_method=plain', $location);
    }

    #[Test]
    public function shouldKeepCodeChallengeGivenByCallerWhenContextHasCodeVerifier(): void
    {
        $context = $this->target->createAuthenticationRequestContext('https://someredirect');

        $location = $this->target->createAuthorizeRedirectResponse([
            'code_challenge' => 'caller-challenge',
            'code_challenge_method' => 'S256',
        ], $context)->getHeaderLine('Location');

        $this->assertStringContainsString('code_challenge=caller-challenge', $location);
        $this->assertSame(1, substr_count($location, 'code_challenge='));
    }

    #[Test]
    public function shouldNotSendNonceWhenUnsafeContextHasNoNonce(): void
    {
        $context = new UnsafeAuthenticationRequestContext('https://context-redirect', 'context-state');

        $location = $this->target->createAuthorizeRedirectResponse([], $context)->getHeaderLine('Location');

        $this->assertStringContainsString('state=context-state', $location);
        $this->assertStringNotContainsString('nonce=', $location);
    }

    #[Test]
    public function shouldNotSendStateWhenUnsafeContextHasNoState(): void
    {
        $context = new UnsafeAuthenticationRequestContext('https://context-redirect', null, 'context-nonce');

        $location = $this->target->createAuthorizeRedirectResponse([], $context)->getHeaderLine('Location');

        $this->assertStringNotContainsString('state=', $location);
        $this->assertStringContainsString('nonce=context-nonce', $location);
        $this->assertNull($this->target->getState());
        $this->assertSame('context-nonce', $this->target->getNonce());
    }

    #[Test]
    public function shouldReturnAuthorizationUrlWhenCallCreateAuthorizeRedirectResponse(): void
    {
        $actual = $this->target->createAuthorizeRedirectResponse([
            'redirect_uri' => 'https://someredirect',
        ]);

        $this->assertSame(302, $actual->getStatusCode());

        $actualLocation = $actual->getHeaderLine('Location');

        $this->assertStringStartsWith('https://somewhere/auth', $actualLocation);
        $this->assertStringContainsStringIgnoringCase('state=', $actualLocation);
        $this->assertStringContainsStringIgnoringCase('response_type=code', $actualLocation);
        $this->assertStringContainsStringIgnoringCase('redirect_uri=', $actualLocation);
        $this->assertStringContainsStringIgnoringCase('client_id=some_id', $actualLocation);
    }

    #[Test]
    public function shouldReturnAuthorizationPostFormWhenCallCreateAuthorizeFormPostResponse(): void
    {
        $actual = $this->target->createAuthorizeFormPostResponse([
            'redirect_uri' => 'https://someredirect',
        ]);

        $this->assertStringContainsStringIgnoringCase(
            '<form method="post" action="https://somewhere/auth">',
            (string)$actual->getBody()
        );
        $this->assertStringContainsStringIgnoringCase('name="state"', (string)$actual->getBody());
        $this->assertStringContainsStringIgnoringCase('name="response_type" value="code"', (string)$actual->getBody());
        $this->assertStringContainsStringIgnoringCase(
            'name="redirect_uri" value="https://someredirect"',
            (string)$actual->getBody()
        );
        $this->assertStringContainsStringIgnoringCase('name="client_id" value="some_id"', (string)$actual->getBody());
    }

    #[Test]
    public function shouldReturnStringStateAndNonceWhenInitAuthorizationParameters(): void
    {
        $this->assertNull($this->target->getNonce());
        $this->assertNull($this->target->getState());

        $this->target->initAuthenticationRequestContext();

        $this->assertNotNull($this->target->getNonce());
        $this->assertNotNull($this->target->getState());
    }

    #[Test]
    public function shouldReturnSameStateAndNonceWhenInitAuthorizationParametersTwice(): void
    {
        $this->target->initAuthenticationRequestContext();

        $expectedNonce = $this->target->getNonce();
        $expectedState = $this->target->getState();

        $this->target->initAuthenticationRequestContext();

        $this->assertSame($expectedNonce, $this->target->getNonce());
        $this->assertSame($expectedState, $this->target->getState());
    }

    #[Test]
    public function shouldReturnPrepareStateAndNonceWhenInitAuthorizationParametersWithOptions(): void
    {
        $expectedNonce = 'some-nonce';
        $expectedState = 'some-state';

        $this->target->initAuthenticationRequestContext([
            'nonce' => $expectedNonce,
            'state' => $expectedState,
        ]);

        $this->assertSame($expectedNonce, $this->target->getNonce());
        $this->assertSame($expectedState, $this->target->getState());
    }
}
