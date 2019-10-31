<?php

namespace Tests\Core;

use OpenIDConnect\Core\Builder;
use OpenIDConnect\Core\Client;
use Tests\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private $target;

    protected function setUp(): void
    {
        $this->target = new Client(
            $this->createProviderMetadata(),
            $this->createClientRegistration([], 'https://someredirect'),
            Builder::createDefaultContainer()
        );
    }

    protected function tearDown(): void
    {
        $this->target = null;
    }

    /**
     * @test
     */
    public function shouldReturnAuthorizationUrlWhenCallSame(): void
    {
        $actual = $this->target->createAuthorizeUri();

        $this->assertStringStartsWith('https://somewhere/auth', (string)$actual);
        $this->assertStringContainsStringIgnoringCase('state=', (string)$actual);
        $this->assertStringContainsStringIgnoringCase('response_type=code', (string)$actual);
        $this->assertStringContainsStringIgnoringCase('redirect_uri=', (string)$actual);
        $this->assertStringContainsStringIgnoringCase('client_id=some_id', (string)$actual);
    }

    /**
     * @test
     */
    public function shouldReturnAuthorizationUrlWhenCallCreateAuthorizeRedirectResponse(): void
    {
        $actual = $this->target->createAuthorizeRedirectResponse();

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
    public function shouldReturnAuthorizationPostFormWhenCallSame(): void
    {
        $actual = $this->target->createAuthorizeForm();

        $this->assertStringContainsStringIgnoringCase('<form method="post" action="https://somewhere/auth">', $actual);
        $this->assertStringContainsStringIgnoringCase('name="state"', $actual);
        $this->assertStringContainsStringIgnoringCase('name="response_type" value="code"', $actual);
        $this->assertStringContainsStringIgnoringCase('name="redirect_uri" value="https://someredirect"', $actual);
        $this->assertStringContainsStringIgnoringCase('name="client_id" value="some_id"', $actual);
    }

    /**
     * @test
     */
    public function shouldReturnAuthorizationPostFormWhenCallCreateAuthorizeFormPostResponse(): void
    {
        $actual = $this->target->createAuthorizeFormPostResponse();

        $this->assertStringContainsStringIgnoringCase('<form method="post" action="https://somewhere/auth">', (string)$actual->getBody());
        $this->assertStringContainsStringIgnoringCase('name="state"', (string)$actual->getBody());
        $this->assertStringContainsStringIgnoringCase('name="response_type" value="code"', (string)$actual->getBody());
        $this->assertStringContainsStringIgnoringCase('name="redirect_uri" value="https://someredirect"', (string)$actual->getBody());
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
