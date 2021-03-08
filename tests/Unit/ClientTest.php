<?php

namespace Tests\Unit;

use InvalidArgumentException;
use Laminas\Diactoros\Request;
use MilesChou\Psr\Http\Client\Testing\MockClient;
use OpenIDConnect\Client;
use OpenIDConnect\Exceptions\OAuth2ClientException;
use OpenIDConnect\Exceptions\OAuth2ServerException;
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
        $this->target = new Client($this->createConfig(), new MockClient());
    }

    protected function tearDown(): void
    {
        $this->target = null;
    }

    /**
     * @test
     */
    public function shouldReturnPreparedStateWhenInitParameters(): void
    {
        $this->target->initAuthorizationParameters([
            'state' => 'expected-state',
        ]);

        $this->assertSame('expected-state', $this->target->getState());
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function shouldReturnTokenSetInstanceWhenCallSendTokenRequest(): void
    {
        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['access_token' => 'some-access-token'])
        );

        $this->target->setHttpClient($mockClient);

        $actual = $this->target->sendTokenRequest([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
        ], []);

        $this->assertSame('some-access-token', $actual['access_token']);
    }

    /**
     * @test
     */
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

        $this->target->sendTokenRequest([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
        ], []);
    }

    /**
     * @test
     */
    public function shouldThrowOAuth2ServerExceptionWhenPayloadIsNotJson(): void
    {
        $this->expectException(OAuth2ServerException::class);

        $mockClient = (new MockClient())->appendResponseWith('not json');

        $this->target->setHttpClient($mockClient);

        $this->target->sendTokenRequest([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
        ], []);
    }

    /**
     * @test
     */
    public function shouldThrowOAuth2ServerExceptionWhenReturnJsonHasErrorKey(): void
    {
        $this->expectException(OAuth2ServerException::class);

        $mockClient = (new MockClient())->appendResponseWithJson(['error' => 'whatever']);

        $this->target->setHttpClient($mockClient);

        $this->target->sendTokenRequest([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
        ], []);
    }

    /**
     * @test
     */
    public function shouldThrowInvalidArgumentExceptionWhenHandleCallbackWithParameterMissingCode(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->target->handleCallback([]);
    }

    /**
     * @test
     */
    public function shouldThrowInvalidArgumentExceptionWhenHandleCallbackWithParameterGivenStateButChecksNot(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['access_token' => 'some-access-token'])
        );

        $this->target->setHttpClient($mockClient);

        $this->target->handleCallback([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
            'state' => 'whatever',
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowOAuth2ClientExceptionWhenHandleCallbackWithChecksGivenStateButParametersNot(): void
    {
        $this->expectException(OAuth2ClientException::class);

        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['access_token' => 'some-access-token'])
        );

        $this->target->setHttpClient($mockClient);

        $this->target->handleCallback([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
        ], [
            'state' => 'whatever',
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowOAuth2ClientExceptionWhenHandleCallbackWithBothGivenButNotSame(): void
    {
        $this->expectException(OAuth2ClientException::class);

        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['access_token' => 'some-access-token'])
        );

        $this->target->setHttpClient($mockClient);

        $this->target->handleCallback([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
            'state' => 'foo',
        ], [
            'state' => 'bar',
        ]);
    }

    /**
     * @test
     */
    public function shouldReturnTokenWhenHandleCallbackWithStateIsNotGiven(): void
    {
        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['access_token' => 'some-access-token'])
        );

        $this->target->setHttpClient($mockClient);

        $actual = $this->target->handleCallback([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
        ]);

        $this->assertSame('some-access-token', $actual->accessToken());
    }

    /**
     * @test
     */
    public function shouldReturnTokenWhenHandleCallbackWithBothGivenAndSame(): void
    {
        $mockClient = (new MockClient())->appendResponseWithJson(
            $this->createFakeTokenSetParameter(['access_token' => 'some-access-token'])
        );

        $this->target->setHttpClient($mockClient);

        $actual = $this->target->handleCallback([
            'code' => 'whatever',
            'redirect_uri' => 'whatever',
            'state' => 'foo',
        ], [
            'state' => 'foo',
        ]);

        $this->assertSame('some-access-token', $actual->accessToken());
    }


    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function shouldReturnStringStateAndNonceWhenInitAuthorizationParameters(): void
    {
        $this->assertNull($this->target->getNonce());
        $this->assertNull($this->target->getState());

        $this->target->initAuthorizationParameters();

        $this->assertNotNull($this->target->getNonce());
        $this->assertNotNull($this->target->getState());
    }

    /**
     * @test
     */
    public function shouldReturnSameStateAndNonceWhenInitAuthorizationParametersTwice(): void
    {
        $this->target->initAuthorizationParameters();

        $expectedNonce = $this->target->getNonce();
        $expectedState = $this->target->getState();

        $this->target->initAuthorizationParameters();

        $this->assertSame($expectedNonce, $this->target->getNonce());
        $this->assertSame($expectedState, $this->target->getState());
    }

    /**
     * @test
     */
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
