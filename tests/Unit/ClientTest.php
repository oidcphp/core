<?php

namespace Tests\Unit;

use Laminas\Diactoros\Request;
use MilesChou\Psr\Http\Client\Testing\MockClient;
use OpenIDConnect\AuthenticationRequestContext;
use OpenIDConnect\Client;
use OpenIDConnect\Exceptions\OAuth2ServerException;
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
        $this->target->initAuthorizationParameters([
            'state' => 'expected-state',
        ]);

        $this->assertSame('expected-state', $this->target->getState());
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

        $this->target->initAuthorizationParameters();

        $this->assertNotNull($this->target->getNonce());
        $this->assertNotNull($this->target->getState());
    }

    #[Test]
    public function shouldReturnSameStateAndNonceWhenInitAuthorizationParametersTwice(): void
    {
        $this->target->initAuthorizationParameters();

        $expectedNonce = $this->target->getNonce();
        $expectedState = $this->target->getState();

        $this->target->initAuthorizationParameters();

        $this->assertSame($expectedNonce, $this->target->getNonce());
        $this->assertSame($expectedState, $this->target->getState());
    }

    #[Test]
    public function shouldReturnPrepareStateAndNonceWhenInitAuthorizationParametersWithOptions(): void
    {
        $expectedNonce = 'some-nonce';
        $expectedState = 'some-state';

        $this->target->initAuthorizationParameters([
            'nonce' => $expectedNonce,
            'state' => $expectedState,
        ]);

        $this->assertSame($expectedNonce, $this->target->getNonce());
        $this->assertSame($expectedState, $this->target->getState());
    }
}
